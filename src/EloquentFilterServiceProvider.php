<?php

namespace Marifyahya\EloquentFilter;

use Illuminate\Support\ServiceProvider;

class EloquentFilterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/eloquent-filter.php' => config_path('eloquent-filter.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../stubs/FilterRequest.stub' => app_path('Http/Requests/FilterRequest.php'),
        ], 'request');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eloquent-filter.php',
            'eloquent-filter'
        );
    }
}
