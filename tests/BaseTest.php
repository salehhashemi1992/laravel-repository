<?php

namespace Salehhashemi\Repository\Tests;

use Orchestra\Testbench\TestCase;
use Salehhashemi\Repository\RepositoryServiceProvider;

abstract class BaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/TestSupport/database/migrations');
        $this->artisan('migrate', ['--database' => 'test'])->run();
    }

    /**
     * Add the package provider.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [RepositoryServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'test');
        $app['config']->set('database.connections.test', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
