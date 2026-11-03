<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

it('builds an icon with every installed style', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    $file = $target.'/beaker-test.blade.php';
    expect($file)->toBeFile();

    $contents = (string) file_get_contents($file);

    expect($contents)
        ->toContain('@props(')
        ->toContain("@case('stroke-rounded')")
        ->toContain("@case('solid-rounded')")
        ->toContain('data-flux-icon')
        ->toContain('viewBox="0 0 24 24"')
        // camelCase Hugeicons attributes are converted to SVG spelling.
        ->toContain('stroke-width="1.5"')
        ->toContain('stroke-linecap="round"')
        ->toContain('fill-rule="evenodd"')
        ->toContain('clip-rule="evenodd"')
        // the React-only `key` attribute is dropped.
        ->not->toContain('key=')
        // the variant match routes Hugeicons + Flux names to a real style.
        ->toContain("'solid-rounded', 'solid' => 'solid-rounded',")
        ->toContain("default => 'stroke-rounded',");
});

it('collapses the match expression when only one style is available', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['home-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    $contents = (string) file_get_contents($target.'/home-test.blade.php');

    expect($contents)
        ->toContain("\$resolved = 'stroke-rounded';")
        ->not->toContain('$resolved = match');
});

it('builds the whole set when no icons are given', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    expect($target.'/beaker-test.blade.php')->toBeFile();
    expect($target.'/home-test.blade.php')->toBeFile();
});

it('can limit generation to a single style', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--styles' => ['solid-rounded'],
        '--force' => true,
    ])->assertSuccessful();

    $contents = (string) file_get_contents($target.'/beaker-test.blade.php');

    expect($contents)
        ->toContain("@case('solid-rounded')")
        ->not->toContain("@case('stroke-rounded')");
});

it('fails on an unknown icon', function (): void {
    $this->artisan('hugeicons:build', [
        'icons' => ['does-not-exist'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $this->tempDir(),
    ])->assertFailed();
});

it('fails when no Hugeicons packages are installed', function (): void {
    $emptyNodeModules = $this->tempDir().'/node_modules';
    mkdir($emptyNodeModules, 0o777, true);

    $this->artisan('hugeicons:build', [
        '--node-modules' => $emptyNodeModules,
        '--target' => $this->tempDir(),
    ])->assertFailed();
});

it('skips existing icons unless --force is passed', function (): void {
    $target = $this->tempDir();
    mkdir($target, 0o777, true);
    file_put_contents($target.'/beaker-test.blade.php', 'ORIGINAL');

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
    ])->assertSuccessful();

    expect((string) file_get_contents($target.'/beaker-test.blade.php'))->toBe('ORIGINAL');

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    expect((string) file_get_contents($target.'/beaker-test.blade.php'))->not->toBe('ORIGINAL');
});

it('never paints the root svg so stroke-only layers stay unfilled', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    $contents = (string) file_get_contents($target.'/beaker-test.blade.php');

    expect(substr_count($contents, '<svg '))->toBe(3)
        ->and(substr_count($contents, 'viewBox="0 0 24 24" fill="none">'))->toBe(3)
        ->and($contents)->not->toContain('viewBox="0 0 24 24" fill="currentColor"');
});

it('supports the duotone-standard style and draws secondary layers first', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--styles' => ['duotone-standard'],
        '--force' => true,
    ])->assertSuccessful();

    $contents = (string) file_get_contents($target.'/beaker-test.blade.php');

    expect($contents)
        ->toContain("@case('duotone-standard')")
        ->toContain("\$resolved = 'duotone-standard';")
        ->and(strpos($contents, 'opacity="0.4"'))->toBeLessThan(strpos($contents, 'd="M5 5H19V19H5V5Z"'));
});

it('routes the duotone-standard variant when built alongside other styles', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    expect((string) file_get_contents($target.'/beaker-test.blade.php'))
        ->toContain("'duotone-standard' => 'duotone-standard',")
        // `duotone` still aliases the rounded style, which is not installed here.
        ->toContain("'duotone-rounded', 'duotone'");
});

it('makes styles it does not know about selectable by name', function (): void {
    $nodeModules = $this->tempDir().'/node_modules';
    (new Filesystem())->copyDirectory($this->fixtureNodeModules(), $nodeModules);

    $future = $nodeModules.'/@hugeicons-pro/core-glyph-future/dist/esm';
    mkdir($future, 0o777, true);
    copy(
        $this->fixtureNodeModules().'/@hugeicons-pro/core-solid-rounded/dist/esm/BeakerTestIcon.js',
        $future.'/BeakerTestIcon.js',
    );

    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $nodeModules,
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    expect((string) file_get_contents($target.'/beaker-test.blade.php'))
        ->toContain("'glyph-future' => 'glyph-future',")
        ->toContain("@case('glyph-future')");
});

it('accepts the PascalCase export name of an icon', function (): void {
    $target = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['HomeTestIcon', 'BeakerTest'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    expect($target.'/home-test.blade.php')->toBeFile()
        ->and($target.'/beaker-test.blade.php')->toBeFile();
});

it('counts modules that are not icon arrays as failed extractions', function (): void {
    $nodeModules = $this->tempDir().'/node_modules';
    $esm = $nodeModules.'/@hugeicons/core-free-icons/dist/esm';
    mkdir($esm, 0o777, true);
    file_put_contents($esm.'/BrokenTestIcon.js', 'export default 42;');

    $this->artisan('hugeicons:build', [
        'icons' => ['broken-test'],
        '--node-modules' => $nodeModules,
        '--target' => $this->tempDir(),
        '--force' => true,
    ])
        ->expectsOutputToContain('Could not extract 1 icon(s)')
        ->assertSuccessful();
});
