<?php

declare(strict_types=1);

namespace Dunn\HugeiconsFlux\Tests;

use Dunn\HugeiconsFlux\Providers\HugeiconsFluxServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var list<string> */
    private array $tempDirs = [];

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        $providers = [HugeiconsFluxServiceProvider::class];

        // Flux (and Livewire, which it depends on) are only needed for the
        // rendering test. Register them when available so the build-command
        // tests still run in a Flux-less environment.
        if (class_exists(\Livewire\LivewireServiceProvider::class)) {
            array_unshift($providers, \Livewire\LivewireServiceProvider::class);
        }

        if (class_exists(\Flux\FluxServiceProvider::class)) {
            $providers[] = \Flux\FluxServiceProvider::class;
        }

        return $providers;
    }

    protected function tearDown(): void
    {
        $files = new Filesystem();

        foreach ($this->tempDirs as $dir) {
            $files->deleteDirectory($dir);
        }

        parent::tearDown();
    }

    /**
     * Absolute path to the fixture node_modules tree.
     */
    protected function fixtureNodeModules(): string
    {
        return __DIR__.'/fixtures/node_modules';
    }

    /**
     * Create a unique temp directory that is removed after the test.
     */
    protected function tempDir(): string
    {
        $dir = sys_get_temp_dir().'/hugeicons-flux-'.bin2hex(random_bytes(6));
        $this->tempDirs[] = $dir;

        return $dir;
    }
}
