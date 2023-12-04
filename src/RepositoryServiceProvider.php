<?php

namespace Salehhashemi\Repository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/repository.php' => config_path('repository.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/repository.php', 'repository');
    }
}
