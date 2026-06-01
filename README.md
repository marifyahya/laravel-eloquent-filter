# Laravel Eloquent Filter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marifyahya/laravel-eloquent-filter.svg)](https://packagist.org/packages/marifyahya/laravel-eloquent-filter)
[![Total Downloads](https://img.shields.io/packagist/dt/marifyahya/laravel-eloquent-filter.svg)](https://packagist.org/packages/marifyahya/laravel-eloquent-filter)
[![License](https://img.shields.io/packagist/l/marifyahya/laravel-eloquent-filter.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](https://github.com/marifyahya/laravel-eloquent-filter)

Elegant search and filter utilities for Laravel Eloquent models.

## Requirements

- PHP: ^8.2
- Laravel components: ^11.0, ^12.0, or ^13.0
- Database: MySQL, PostgreSQL, SQLite, or SQL Server

## Installation

```bash
composer require marifyahya/laravel-eloquent-filter
```

Optionally publish the config and request stub:

```bash
php artisan vendor:publish --provider="Marifyahya\EloquentFilter\EloquentFilterServiceProvider" --tag=config
php artisan vendor:publish --provider="Marifyahya\EloquentFilter\EloquentFilterServiceProvider" --tag=request
```

## Model Setup

Add the `HasEloquentFilter` trait to your model and whitelist the fields that can be filtered, searched, sorted, or used as date ranges.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Marifyahya\EloquentFilter\Traits\HasEloquentFilter;

class Post extends Model
{
    use HasEloquentFilter;

    protected $filterableFields = ['id', 'status', 'category_id'];
    protected $sortableFields = ['id', 'title', 'status', 'created_at'];
    protected $searchableFields = ['title', 'content'];
    protected $dateRangeFields = ['created_at', 'published_at'];
    protected $normalizeFilterKeys = true;

    protected $filterableMap = [
        'post_id' => 'id',
        'author_name' => ['author_firstname', 'author_lastname'],
    ];
}
```

If `sortableFields` is not set, the package falls back to `filterableFields`.

## Usage

Use `filter()` in your controller before pagination or `get()`.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        return Post::filter($request->all())->paginate(15);
    }
}
```

```php
use App\Models\Post;

// Auto from request()->all()
$posts = Post::filter()->get();

// With custom config
$posts = Post::filter($request->all(), [
    'searchable' => ['title', 'content'],
    'filterable' => ['status', 'category_id'],
    'sortable' => ['title', 'views', 'created_at'],
    'date_ranges' => ['created_at'],
    'normalize_keys' => true,
])->paginate(15);
```

## Model Properties

| Property | Purpose |
| --- | --- |
| `$filterableFields` | Columns allowed for exact, operator, comma-separated, array, and null filters. |
| `$sortableFields` | Columns allowed for `sort` and `sort_by`. Falls back to `$filterableFields` when not set. |
| `$searchableFields` | Columns searched by the `search` query parameter. |
| `$dateRangeFields` | Date columns allowed for `{field}_from` and `{field}_to` filters. |
| `$filterableMap` | Public aliases mapped to real columns or multiple columns. |
| `$customFilters` | Custom filter classes or callbacks keyed by request parameter. |
| `$normalizeFilterKeys` | Converts camelCase request keys to snake_case before filtering. |

## Filter Capabilities

### Search

Search applies a partial `LIKE` query across fields listed in `searchableFields` or the `searchable` config key.

```http
GET /posts?search=laravel
```

### Exact Match

```http
GET /posts?status=published
```

### Multiple Values

Comma-separated values use `WHERE IN`. Prefix the value with `!` to use `WHERE NOT IN`.

```http
GET /posts?status=active,pending,draft
GET /posts?status=!draft,archived
GET /posts?status[]=active&status[]=pending
```

### NULL / NOT NULL

```http
GET /posts?deleted_at=null
GET /posts?deleted_at=!null
```

### Operators

```http
GET /posts?views=>100
GET /posts?views=<50
GET /posts?views=>=10
GET /posts?views=<=100
GET /posts?views=!=0
GET /posts?status=!=draft,archived
GET /posts?title=likeLaravel
```

### Between

```http
GET /posts?views=<>10,100
```

### Date Range

Date ranges are enabled only for fields listed in `dateRangeFields` or the `date_ranges` config key.

```http
GET /posts?created_at_from=2024-01-01&created_at_to=2024-12-31
```

### Camel Case Request Keys

Enable key normalization when your API receives camelCase request keys from a frontend client. Request keys are normalized to snake_case before filtering.

You can enable it on the model:

```php
class Post extends Model
{
    use HasEloquentFilter;

    protected $normalizeFilterKeys = true;
}
```

Or enable it for a single query:

```php
Post::filter($request->all(), [
    'normalize_keys' => true,
    'filterable' => ['category_id'],
    'sortable' => ['created_at'],
    'date_ranges' => ['created_at'],
]);
```

```http
GET /posts?categoryId=2&createdAtFrom=2024-01-01&createdAtTo=2024-12-31&sortBy=created_at&sortDir=desc
```

### Sorting

Sorting is ignored unless the requested field is listed in `sortableFields` or the `sortable` config key.

```http
GET /posts?sort_by=views&sort_dir=desc
GET /posts?sort_by=views&sort_dir=DESC
GET /posts?sort=-created_at
GET /posts?sort=title
```

### Soft Deletes

These filters only apply to models that use Laravel's `SoftDeletes` trait.

```http
GET /posts?trashed=only
GET /posts?trashed=with
```

### Relation Existence

```php
Post::filter($request->all(), [
    'relation_exists' => ['comments', 'likes'],
]);
```

```http
GET /posts?has_comments=true
GET /posts?has_comments=false
```

### Relation Fields

```php
Post::filter($request->all(), [
    'relations' => [
        'author' => ['status'],
    ],
]);
```

```http
GET /posts?author.status=active
```

Relation filtering is supported, but sorting by relation columns is not supported yet.

### Filterable Map

Use a map to expose public aliases without exposing internal column names. A multi-column alias searches each mapped column with `LIKE` and groups them with `OR`.

```php
class User extends Model
{
    protected $filterableMap = [
        'name' => ['firstname', 'lastname'],
        'user' => 'user_id',
    ];
}
```

```http
GET /users?name=john
GET /users?user=10
```

### Custom Filter Method

Model methods named `filter{Field}` take priority over the default filtering behavior.

```php
class Post extends Model
{
    public function filterStatus($query, $value): void
    {
        if ($value === 'published,reviewed') {
            $query->whereIn('status', ['published', 'reviewed'])
                ->where('approved', true);

            return;
        }

        $query->where('status', $value);
    }
}
```

```http
GET /posts?status=published,reviewed
```

### Custom Filter Class

```php
class PopularFilter
{
    public function apply($query, $value): void
    {
        $query->where('views', '>', 1000);
    }
}
```

```php
Post::filter($request->all(), [
    'custom_filters' => [
        'popular' => \App\Filters\PopularFilter::class,
    ],
]);
```

### Custom Filter Callback

```php
Post::filter($request->all(), [
    'custom_filters' => [
        'title' => fn($query, $value) => $query->where('title', 'LIKE', "%{$value}%"),
    ],
]);
```

## Security Notes

- Only whitelist columns that are safe to expose to users.
- Sorting is whitelisted separately from filtering.
- Unknown filters and non-sortable sort fields are ignored.
- Use custom filters for complex authorization-aware conditions.

## Testing

```bash
./vendor/bin/phpunit
```

## License

MIT
