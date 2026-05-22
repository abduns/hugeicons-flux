<?php

declare(strict_types=1);

it('publishes the boost skill into a target directory', function (): void {
    $target = $this->tempDir().'/skills/hugeicons-flux';

    $this->artisan('hugeicons:boost-skill', [
        '--target' => $target,
    ])->assertSuccessful();

    $file = $target.'/SKILL.md';

    expect($file)->toBeFile()
        ->and((string) file_get_contents($file))
        ->toContain('name: hugeicons-flux')
        ->toContain('php artisan hugeicons:build {name}');
});

it('does not overwrite an existing boost skill unless forced', function (): void {
    $target = $this->tempDir().'/skills/hugeicons-flux';
    mkdir($target, 0o777, true);
    file_put_contents($target.'/SKILL.md', 'CUSTOM');

    $this->artisan('hugeicons:boost-skill', [
        '--target' => $target,
    ])->assertFailed();

    expect((string) file_get_contents($target.'/SKILL.md'))->toBe('CUSTOM');

    $this->artisan('hugeicons:boost-skill', [
        '--target' => $target,
        '--force' => true,
    ])->assertSuccessful();

    expect((string) file_get_contents($target.'/SKILL.md'))->toContain('name: hugeicons-flux');
});
