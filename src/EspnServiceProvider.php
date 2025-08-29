<?php

namespace MichaelCrowcroft\EspnLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class EspnServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('espn-laravel')
            ->hasConfigFile('espn');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(Espn::class, function ($app) {
            return new Espn(config('espn'));
        });

        // Also bind a string key for easy resolution and facade
        $this->app->alias(Espn::class, 'espn');
    }
}
