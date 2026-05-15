<?php

declare(strict_types=1);

namespace Dunn\HugeiconsFlux\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use JsonException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Generates Flux icon components from the Hugeicons npm packages installed in
 * the host application's `node_modules`.
 *
 * The free `@hugeicons/core-free-icons` package contributes the `stroke-rounded`
 * style; each installed `@hugeicons-pro/core-*` package contributes one further
 * style. Every requested icon is written as a single Blade file whose `variant`
 * prop switches between the styles that were found.
 */
#[AsCommand(name: 'hugeicons:build')]
final class BuildIconsCommand extends Command
{
    protected $signature = 'hugeicons:build
        {icons?* : Icon names to build (kebab-case, e.g. home-01). Builds every icon when omitted.}
        {--target= : Directory the Blade files are written to. Defaults to resources/views/flux/icon/hugeicons.}
        {--node-modules= : Path to the node_modules directory. Defaults to the application base path.}
        {--styles=* : Limit generation to specific Hugeicons styles (e.g. solid-rounded). Defaults to every installed style.}
        {--force : Overwrite icons that already exist.}';

    protected $description = 'Generate Flux icon components from the installed Hugeicons npm packages.';

    /**
     * Hugeicons style => the `variant` values that should resolve to it.
     *
     * The first key is also the canonical ordering for styles, and the
     * preferred fallback when an icon is missing a requested style.
     *
     * @var array<string, list<string>>
     */
    private const STYLE_ALIASES = [
        'stroke-rounded' => ['stroke-rounded', 'stroke', 'outline', 'mini', 'micro'],
        'stroke-sharp' => ['stroke-sharp'],
        'stroke-standard' => ['stroke-standard'],
        'solid-rounded' => ['solid-rounded', 'solid'],
        'solid-sharp' => ['solid-sharp'],
        'solid-standard' => ['solid-standard'],
        'bulk-rounded' => ['bulk-rounded', 'bulk'],
        'duotone-rounded' => ['duotone-rounded', 'duotone'],
        'twotone-rounded' => ['twotone-rounded', 'twotone'],
    ];

    /**
     * SVG attributes that must keep their camelCase spelling rather than being
     * kebab-cased like presentation attributes.
     *
     * @var list<string>
     */
    private const CAMEL_CASE_ATTRIBUTES = [
        'viewBox', 'preserveAspectRatio', 'gradientUnits', 'gradientTransform',
        'spreadMethod', 'patternUnits', 'patternContentUnits', 'patternTransform',
        'clipPathUnits', 'maskUnits', 'maskContentUnits', 'markerUnits', 'refX', 'refY',
    ];

    /** Number of icons handed to the Node extractor per invocation. */
    private const CHUNK_SIZE = 400;

    public function handle(Filesystem $files): int
    {
        $node = (new ExecutableFinder())->find('node');

        if ($node === null) {
            $this->components->error('Node.js is required to run hugeicons:build but "node" was not found on PATH.');

            return self::FAILURE;
        }

        $nodeModules = $this->resolveNodeModules();

        if ($nodeModules === null) {
            return self::FAILURE;
        }

        $packages = $this->discoverStylePackages($nodeModules);

        if ($packages === []) {
            $this->components->error(
                "No Hugeicons packages found in {$nodeModules}. "
                .'Install @hugeicons/core-free-icons or any @hugeicons-pro/core-* package.'
            );

            return self::FAILURE;
        }

        $this->components->info('Styles: '.implode(', ', array_keys($packages)));

        $index = $this->buildIconIndex($packages);
        $requested = $this->resolveRequestedIcons($index);

        if ($requested === null) {
            return self::FAILURE;
        }

        $target = $this->resolveTarget();
        $files->ensureDirectoryExists($target);

        $stub = (string) file_get_contents($this->stubPath());
        $force = (bool) $this->option('force');

        $built = 0;
        $skipped = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar(count($requested));
        $bar->start();

        foreach (array_chunk($requested, self::CHUNK_SIZE, true) as $chunk) {
            $pending = [];

            foreach ($chunk as $module => $name) {
                if (! $force && is_file($target.'/'.$name.'.blade.php')) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                $pending[$module] = $name;
            }

            if ($pending === []) {
                continue;
            }

            $extracted = $this->extract($node, $packages, array_keys($pending));

            foreach ($pending as $module => $name) {
                $styles = $extracted[$module] ?? [];

                if ($styles === []) {
                    $failed++;
                    $bar->advance();

                    continue;
                }

                $files->put($target.'/'.$name.'.blade.php', $this->renderBlade($stub, $styles));
                $built++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->components->info("Built {$built} icon(s) into {$target}.");

        if ($skipped > 0) {
            $this->components->warn("Skipped {$skipped} existing icon(s). Pass --force to overwrite.");
        }

        if ($failed > 0) {
            $this->components->warn("Could not extract {$failed} icon(s) from the installed packages.");
        }

        return self::SUCCESS;
    }

    /**
     * Resolve and validate the node_modules directory.
     */
    private function resolveNodeModules(): ?string
    {
        $option = $this->option('node-modules');
        $path = is_string($option) && $option !== ''
            ? $option
            : (function_exists('base_path') ? base_path('node_modules') : getcwd().'/node_modules');

        if (! is_dir($path)) {
            $this->components->error("node_modules directory not found at {$path}.");

            return null;
        }

        return rtrim($path, '/');
    }

    /**
     * Resolve the directory the generated Blade files are written to.
     */
    private function resolveTarget(): string
    {
        $option = $this->option('target');

        if (is_string($option) && $option !== '') {
            return rtrim($option, '/');
        }

        return function_exists('resource_path')
            ? resource_path('views/flux/icon/hugeicons')
            : getcwd().'/resources/views/flux/icon/hugeicons';
    }

    /**
     * Discover the installed Hugeicons style packages.
     *
     * Pro packages take precedence over the free package for a shared style.
     *
     * @return array<string, string> style => absolute package directory
     */
    private function discoverStylePackages(string $nodeModules): array
    {
        $packages = [];

        foreach (glob($nodeModules.'/@hugeicons-pro/core-*', GLOB_ONLYDIR) ?: [] as $dir) {
            $style = (string) preg_replace('/^core-/', '', basename($dir));

            if (is_dir($dir.'/dist/esm')) {
                $packages[$style] = $dir;
            }
        }

        $free = $nodeModules.'/@hugeicons/core-free-icons';

        if (! isset($packages['stroke-rounded']) && is_dir($free.'/dist/esm')) {
            $packages['stroke-rounded'] = $free;
        }

        $only = array_values(array_filter(array_map('strval', (array) $this->option('styles'))));

        if ($only !== []) {
            $packages = array_intersect_key($packages, array_flip($only));
        }

        uksort($packages, fn (string $a, string $b): int => $this->styleRank($a) <=> $this->styleRank($b));

        return $packages;
    }

    /**
     * Build the icon index across every installed package.
     *
     * @param  array<string, string>  $packages
     * @return array<string, string> module basename => kebab-case icon name
     */
    private function buildIconIndex(array $packages): array
    {
        $index = [];

        foreach ($packages as $dir) {
            foreach (glob($dir.'/dist/esm/*Icon.js') ?: [] as $file) {
                $module = basename($file, '.js');

                if (! isset($index[$module])) {
                    $index[$module] = $this->toKebab(substr($module, 0, -4));
                }
            }
        }

        ksort($index);

        return $index;
    }

    /**
     * Resolve the icons requested on the command line against the index.
     *
     * @param  array<string, string>  $index  module basename => kebab name
     * @return array<string, string>|null  module basename => kebab name, or null on an unknown icon
     */
    private function resolveRequestedIcons(array $index): ?array
    {
        $arguments = array_values(array_filter(array_map('strval', (array) $this->argument('icons'))));

        if ($arguments === []) {
            return $index;
        }

        $byName = array_flip($index);
        $resolved = [];
        $unknown = [];

        foreach ($arguments as $argument) {
            $name = $this->toKebab($argument);

            if (isset($byName[$name])) {
                $resolved[$byName[$name]] = $name;
            } else {
                $unknown[] = $argument;
            }
        }

        if ($unknown !== []) {
            $this->components->error('Unknown icon(s): '.implode(', ', $unknown));

            return null;
        }

        return $resolved;
    }

    /**
     * Run the Node extractor for a batch of icon modules.
     *
     * @param  array<string, string>  $packages  style => package directory
     * @param  list<string>  $modules  module basenames to extract
     * @return array<string, array<string, list<array{0: string, 1: array<string, scalar>}>>>
     */
    private function extract(string $node, array $packages, array $modules): array
    {
        try {
            $payload = json_encode(['packages' => $packages, 'icons' => $modules], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to encode the extractor request: '.$e->getMessage(), previous: $e);
        }

        $process = new Process([$node, $this->extractorPath()]);
        $process->setInput($payload);
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('The Hugeicons extractor failed: '.trim($process->getErrorOutput()));
        }

        try {
            /** @var array<string, array<string, list<array{0: string, 1: array<string, scalar>}>>> $decoded */
            $decoded = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('The Hugeicons extractor returned invalid JSON: '.$e->getMessage(), previous: $e);
        }

        return $decoded;
    }

    /**
     * Render a single icon's Blade file from the stub.
     *
     * @param  array<string, list<array{0: string, 1: array<string, scalar>}>>  $styles
     */
    private function renderBlade(string $stub, array $styles): string
    {
        $available = array_keys($styles);
        usort($available, fn (string $a, string $b): int => $this->styleRank($a) <=> $this->styleRank($b));

        $default = in_array('stroke-rounded', $available, true) ? 'stroke-rounded' : $available[0];

        return str_replace(
            ['__RESOLVED__', '__SWITCH_CASES__'],
            [
                $this->buildResolvedExpression($available, $default),
                $this->buildSwitchCases($styles, $available),
            ],
            $stub,
        );
    }

    /**
     * Build the `$resolved = ...` PHP expression that maps a `variant` value to
     * one of the styles actually available for this icon.
     *
     * @param  list<string>  $available
     */
    private function buildResolvedExpression(array $available, string $default): string
    {
        if (count($available) === 1) {
            return "\$resolved = '{$default}';";
        }

        $groups = [];

        foreach (self::STYLE_ALIASES as $style => $variants) {
            $resolved = in_array($style, $available, true) ? $style : $default;

            foreach ($variants as $variant) {
                $groups[$resolved][] = $variant;
            }
        }

        $arms = '';

        foreach ($available as $style) {
            if (empty($groups[$style])) {
                continue;
            }

            $list = implode(', ', array_map(static fn (string $v): string => "'{$v}'", $groups[$style]));
            $arms .= "        {$list} => '{$style}',\n";
        }

        $arms .= "        default => '{$default}',";

        return "\$resolved = match (\$variant) {\n{$arms}\n    };";
    }

    /**
     * Build the `@case(...)` blocks — one `<svg>` per available style.
     *
     * @param  array<string, list<array{0: string, 1: array<string, scalar>}>>  $styles
     * @param  list<string>  $available
     */
    private function buildSwitchCases(array $styles, array $available): string
    {
        $cases = [];

        foreach ($available as $style) {
            $fill = str_starts_with($style, 'stroke-') ? 'none' : 'currentColor';

            $elements = '';

            foreach ($styles[$style] as [$tag, $attributes]) {
                $elements .= '    '.$this->renderElement($tag, $attributes)."\n";
            }

            $svg = '<svg {{ $attributes->class($classes) }} data-flux-icon aria-hidden="true"'
                .' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="'.$fill.'">'."\n"
                .$elements
                .'</svg>';

            $cases[] = "@case('{$style}')\n{$svg}\n@break";
        }

        return implode("\n", $cases);
    }

    /**
     * Render a single SVG child element from a Hugeicons `[tag, attrs]` tuple.
     *
     * @param  array<string, scalar>  $attributes
     */
    private function renderElement(string $tag, array $attributes): string
    {
        $rendered = [];

        foreach ($attributes as $name => $value) {
            if ($name === 'key') {
                continue;
            }

            $rendered[] = $this->attributeName($name)
                .'="'.htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE).'"';
        }

        return '<'.$tag.($rendered === [] ? '' : ' '.implode(' ', $rendered)).' />';
    }

    /**
     * Convert a Hugeicons (React-style camelCase) attribute name to its SVG
     * spelling, kebab-casing everything except the known camelCase attributes.
     */
    private function attributeName(string $name): string
    {
        if (in_array($name, self::CAMEL_CASE_ATTRIBUTES, true)) {
            return $name;
        }

        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
    }

    /**
     * Convert a PascalCase Hugeicons name (without the `Icon` suffix) to the
     * kebab-case component name, e.g. `Home01` => `home-01`, `AiSearch02` =>
     * `ai-search-02`. Idempotent for already-kebab-cased input.
     */
    private function toKebab(string $name): string
    {
        $name = (string) preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $name);
        $name = (string) preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1-$2', $name);
        $name = (string) preg_replace('/([a-zA-Z])([0-9])/', '$1-$2', $name);
        $name = (string) preg_replace('/([0-9])([a-zA-Z])/', '$1-$2', $name);

        return strtolower($name);
    }

    /**
     * Canonical ordering rank for a style (unknown styles sort last).
     */
    private function styleRank(string $style): int
    {
        $rank = array_search($style, array_keys(self::STYLE_ALIASES), true);

        return $rank === false ? count(self::STYLE_ALIASES) : $rank;
    }

    private function extractorPath(): string
    {
        return dirname(__DIR__, 2).'/bin/extract.mjs';
    }

    private function stubPath(): string
    {
        return dirname(__DIR__, 2).'/stubs/icon.blade.php.stub';
    }
}
