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
        '--target' => $base.'/icon/hugeicon',
        '--force' => true,
    ])->assertSuccessful();

    // Register the freshly built icons under Flux's `flux` component namespace.
    Blade::anonymousComponentPath($base, 'flux');

    $html = Blade::render('<flux:icon.hugeicon.beaker-test />');

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
        '--target' => $base.'/icon/hugeicon',
        '--force' => true,
    ])->assertSuccessful();

    Blade::anonymousComponentPath($base, 'flux');

    // The free fixture style renders the stroke path...
    expect(Blade::render('<flux:icon.hugeicon.beaker-test variant="stroke-rounded" />'))
        ->toContain('fill="none"');

    // ...and the Pro fixture style renders the solid path.
    expect(Blade::render('<flux:icon.hugeicon.beaker-test variant="solid-rounded" />'))
        ->toContain('fill-rule="evenodd"');

    // An unknown variant falls back to the default style without erroring.
    expect(Blade::render('<flux:icon.hugeicon.beaker-test variant="micro" />'))
        ->toContain('<svg');
});
