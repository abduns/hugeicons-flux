<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

beforeEach(function (): void {
    if (! class_exists(\Flux\Flux::class)) {
        $this->markTestSkipped('livewire/flux is not installed.');
    }
});

it('renders a generated icon as a flux component', function (): void {
    $base = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $base.'/icon/hugeicons',
        '--force' => true,
    ])->assertSuccessful();

    // Register the freshly built icons under Flux's `flux` component namespace.
    Blade::anonymousComponentPath($base, 'flux');

    $html = Blade::render('<flux:icon.hugeicons.beaker-test />');

    expect($html)
        ->toContain('<svg')
        ->toContain('data-flux-icon')
        ->toContain('viewBox="0 0 24 24"')
        ->toContain('stroke-width="1.5"');
});

it('switches the rendered markup based on the variant prop', function (): void {
    $base = $this->tempDir();

    $this->artisan('hugeicons:build', [
        'icons' => ['beaker-test'],
        '--node-modules' => $this->fixtureNodeModules(),
        '--target' => $base.'/icon/hugeicons',
        '--force' => true,
    ])->assertSuccessful();

    Blade::anonymousComponentPath($base, 'flux');

    // The free fixture style renders the stroke path...
    expect(Blade::render('<flux:icon.hugeicons.beaker-test variant="stroke-rounded" />'))
        ->toContain('stroke-width="1.5"')
        ->not->toContain('fill-rule="evenodd"');

    // ...the Pro fixture style renders the solid path...
    expect(Blade::render('<flux:icon.hugeicons.beaker-test variant="solid" />'))
        ->toContain('fill-rule="evenodd"')
        ->not->toContain('stroke-width="1.5"');

    // ...and the duotone-standard fixture renders its secondary layer.
    expect(Blade::render('<flux:icon.hugeicons.beaker-test variant="duotone-standard" />'))
        ->toContain('opacity="0.4"');

    // An unknown variant falls back to the default style without erroring.
    expect(Blade::render('<flux:icon.hugeicons.beaker-test variant="micro" />'))
        ->toContain('<svg');
});

it('renders the bundled free icons without a build step', function (): void {
    expect(Blade::render('<flux:icon.hugeicons.home-01 class="size-8 text-blue-500" />'))
        ->toContain('<svg')
        ->toContain('data-flux-icon')
        ->toContain('size-8 text-blue-500')
        ->toContain('fill="none"')
        ->toContain('stroke-width="1.5"');
});

it('falls back to stroke-rounded for a Pro variant that was not built', function (): void {
    expect(Blade::render('<flux:icon.hugeicons.home-01 variant="solid-rounded" />'))
        ->toContain('stroke-width="1.5"');
});
