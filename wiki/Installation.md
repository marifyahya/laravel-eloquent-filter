# Installation

Install the package with Composer:

```bash
composer require marifyahya/laravel-eloquent-filter
```

Laravel should auto-discover the service provider.

## Optional Publish Command

Publish the request stub:

```bash
php artisan vendor:publish --provider="Marifyahya\EloquentFilter\EloquentFilterServiceProvider" --tag=request
```

The package is configured from each model or from the second argument passed to `filter()`. No global config options are required.

## Requirements

- PHP `^8.2`
- Laravel components `^11.0`, `^12.0`, or `^13.0`
- MySQL, PostgreSQL, SQLite, or SQL Server
