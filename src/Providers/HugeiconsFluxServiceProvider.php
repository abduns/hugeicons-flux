<?php

declare(strict_types=1);

namespace Dunn\HugeiconsFlux\Providers;

use Dunn\HugeiconsFlux\Console\BuildIconsCommand;
use Dunn\HugeiconsFlux\Console\PublishBoostSkillCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the package's bundled Hugeicons Blade files into Flux's `flux`
 * anonymous-component namespace and registers the `hugeicons:build` command.
 */
final class HugeiconsFluxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the bundled icons after every provider has booted, so the
        // application's own `resources/views/flux` path (registered by Flux's
        // service provider) keeps priority. That lets a host app override any
        // icon by name, and lets Pro builds generated into the app win over
        // the free Stroke Rounded set bundled here.
        $this->app->booted(function (): void {
            Blade::anonymousComponentPath($this->fluxViewPath(), 'flux');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                BuildIconsCommand::class,
                PublishBoostSkillCommand::class,
            ]);
        }
    }

    /**
     * Absolute path to the package's `resources/views/flux` directory.
     */
    private function fluxViewPath(): string
    {
        return dirname(__DIR__, 2).'/resources/views/flux';
    }
}
