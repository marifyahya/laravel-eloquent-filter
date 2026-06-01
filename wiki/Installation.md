# Installation

Install the package with Composer:

```bash
composer require marifyahya/laravel-eloquent-filter
```

Laravel should auto-discover the service provider.

## Optional Publish Commands

Publish the config file:

```bash
php artisan vendor:publish --provider="Marifyahya\EloquentFilter\EloquentFilterServiceProvider" --tag=config
```

Publish the request stub:

```bash
php artisan vendor:publish --provider="Marifyahya\EloquentFilter\EloquentFilterServiceProvider" --tag=request
```

## Requirements

- PHP `^8.2`
- Laravel components `^11.0`, `^12.0`, or `^13.0`
- MySQL, PostgreSQL, SQLite, or SQL Server

