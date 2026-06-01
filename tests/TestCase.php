<?php

namespace Marifyahya\EloquentFilter\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Marifyahya\EloquentFilter\EloquentFilterServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            EloquentFilterServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
