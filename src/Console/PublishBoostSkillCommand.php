<?php

declare(strict_types=1);

namespace Dunn\HugeiconsFlux\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'hugeicons:boost-skill')]
final class PublishBoostSkillCommand extends Command
{
    protected $signature = 'hugeicons:boost-skill
        {--target= : Directory where SKILL.md should be written. Defaults to .ai/skills/hugeicons-flux.}
        {--force : Overwrite an existing skill file.}';

    protected $description = 'Publish the Hugeicons Flux Agent Skill into the application .ai/skills directory.';

    public function handle(Filesystem $files): int
    {
        $source = $this->sourceSkillPath();
        $target = $this->targetSkillPath();

        if (! is_file($source)) {
            $this->components->error("Hugeicons Flux skill not found at {$source}.");

            return self::FAILURE;
        }

        if (is_file($target) && ! (bool) $this->option('force')) {
            $this->components->warn("Skill already exists at {$target}. Pass --force to overwrite.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($target));
        $files->copy($source, $target);

        $this->components->info("Published Hugeicons Flux skill to {$target}.");

        return self::SUCCESS;
    }

    private function sourceSkillPath(): string
    {
        return dirname(__DIR__, 2).'/resources/boost/skills/hugeicons-flux/SKILL.md';
    }

    private function targetSkillPath(): string
    {
        $option = $this->option('target');
        $target = is_string($option) && $option !== ''
            ? $option
            : $this->basePath('.ai/skills/hugeicons-flux');

        return rtrim($target, '/').'/SKILL.md';
    }

    private function basePath(string $path): string
    {
        if (function_exists('base_path')) {
            return base_path($path);
        }

        return getcwd().'/'.$path;
    }
}
