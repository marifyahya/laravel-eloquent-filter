# Laravel Eloquent Filter

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marifyahya/laravel-eloquent-filter.svg)](https://packagist.org/packages/marifyahya/laravel-eloquent-filter)
[![Total Installs](https://img.shields.io/packagist/dt/marifyahya/laravel-eloquent-filter?label=installs&cacheSeconds=3600)](https://packagist.org/packages/marifyahya/laravel-eloquent-filter)
[![License](https://img.shields.io/packagist/l/marifyahya/laravel-eloquent-filter.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](https://github.com/marifyahya/laravel-eloquent-filter)

Elegant, whitelisted search and filter utilities for Laravel Eloquent models.

## Requirements

- PHP `^8.2`
- Laravel components `^11.0`, `^12.0`, or `^13.0`
- MySQL, PostgreSQL, SQLite, or SQL Server

## Installation

```bash
composer require marifyahya/laravel-eloquent-filter
```

Optionally publish the request stub:

```bash
php artisan vendor:publish --provider="Marifyahya\EloquentFilter\EloquentFilterServiceProvider" --tag=request
```

The package is configured from each model or from the second argument passed to `filter()`. No global config options are required.

## Quick Start

Add the `HasEloquentFilter` trait and define the allowed fields on your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Marifyahya\EloquentFilter\Traits\HasEloquentFilter;

class Post extends Model
{
    use HasEloquentFilter;

    protected $filterableFields = ['id', 'status', 'category_id', 'views', 'is_featured'];
    protected $sortableFields = ['id', 'title', 'status', 'views', 'created_at'];
    protected $searchableFields = ['title', 'content'];
    protected $dateRangeFields = ['created_at', 'published_at'];
    protected $normalizeFilterKeys = true;

    protected $filterableMap = [
        'post_id' => 'id',
        'q' => ['title', 'content'],
    ];
}
```

Use `filter()` before `paginate()` or `get()`:

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

Example requests:

```http
GET /posts?search=laravel
GET /posts?status=published
GET /posts?status=published,draft
GET /posts?views=>100
GET /posts?created_at_from=2024-01-01&created_at_to=2024-12-31
GET /posts?sort=-created_at
GET /posts?sort_by=views&sort_dir=desc
```

Combined request:

```http
GET /posts?search=laravel&status=published&views=>100&created_at_from=2024-01-01&sort=-created_at
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

You can also pass configuration per query:

```php
$posts = Post::filter($request->all(), [
    'searchable' => ['title', 'content'],
    'filterable' => ['status', 'category_id'],
    'sortable' => ['title', 'views', 'created_at'],
    'date_ranges' => ['created_at'],
    'normalize_keys' => true,
])->paginate(15);
```

## Filtering Basics

`filterableFields` is an allowlist. It controls which columns may be filtered with exact values, comparison operators, comma-separated values, arrays, null checks, and between checks.

The request value decides the filter behavior:

| Request value | Behavior |
| --- | --- |
| `published` | Exact match: `where('field', 'published')` |
| `>100` | Greater than: `where('field', '>', 100)` |
| `>=100` | Greater than or equal: `where('field', '>=', 100)` |
| `<50` | Less than: `where('field', '<', 50)` |
| `<=50` | Less than or equal: `where('field', '<=', 50)` |
| `!=draft` | Not equal: `where('field', '!=', 'draft')` |
| `active,pending` | In list: `whereIn('field', ['active', 'pending'])` |
| `!draft,archived` | Not in list: `whereNotIn('field', ['draft', 'archived'])` |
| `null` | Is null: `whereNull('field')` |
| `!null` | Is not null: `whereNotNull('field')` |
| `<>10,100` | Between: `whereBetween('field', [10, 100])` |

Field-level `LIKE` filters are intentionally not part of the core syntax. Use `search` for global text search, or custom filters when a specific field should use `LIKE`.

Example model:

```php
protected $filterableFields = [
    'name',
    'category',
    'status',
    'views',
];
```

Example requests:

```http
GET /posts?status=published
GET /posts?category=tech
GET /posts?views=>100
```

## Search

Search applies one keyword across all fields listed in `searchableFields` or the `searchable` config key.

```php
protected $searchableFields = [
    'name',
    'category',
];
```

```http
GET /posts?search=laravel
```

This searches both `name` and `category` using `OR`:

```sql
WHERE (
    name LIKE '%laravel%'
    OR category LIKE '%laravel%'
)
```

Use `search` when one keyword should be searched across multiple fields.

## Exact Match

Exact match filters compare a request value directly with a whitelisted column.

```http
GET /posts?status=published
GET /posts?category=tech
```

These apply:

```sql
WHERE status = 'published'
WHERE category = 'tech'
```

Multiple exact filters are combined using `AND`:

```http
GET /posts?status=published&category=tech
```

```sql
WHERE status = 'published'
AND category = 'tech'
```

## Field-Level LIKE

The package keeps field-level `LIKE` matching as custom behavior. This keeps the core syntax simple and lets each project decide which fields should use partial matching.

Define a custom filter method on your model:

```php
class Post extends Model
{
    use HasEloquentFilter;

    protected $filterableFields = ['name', 'category'];

    public function filterName($query, $value): void
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    public function filterCategory($query, $value): void
    {
        $query->where('category', 'LIKE', "%{$value}%");
    }
}
```

Then use clean request values:

```http
GET /posts?name=contoh&category=tech
```

This applies:

```sql
WHERE name LIKE '%contoh%'
AND category LIKE '%tech%'
```

You can also use `custom_filters` callbacks when you do not want to place filter methods on the model.

## Multiple Values

Comma-separated values use `WHERE IN`:

```http
GET /posts?status=active,pending,draft
GET /posts?status[]=active&status[]=pending
```

Prefix the value with `!` to use `WHERE NOT IN`:

```http
GET /posts?status=!draft,archived
```

## Operators

```http
GET /posts?views=>100
GET /posts?views=<50
GET /posts?views=>=10
GET /posts?views=<=100
GET /posts?views=!=0
GET /posts?status=!=draft,archived
```

## NULL / NOT NULL

```http
GET /posts?deleted_at=null
GET /posts?deleted_at=!null
```

## Between

```http
GET /posts?views=<>10,100
```

## Date Range

Date ranges are enabled only for fields listed in `dateRangeFields` or the `date_ranges` config key.

```http
GET /posts?created_at_from=2024-01-01&created_at_to=2024-12-31
```

## Camel Case Request Keys

Enable key normalization when your API receives camelCase request keys from a frontend client. Request keys are normalized to snake_case before filtering.

Enable it on the model:

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

The request above is normalized to:

```text
categoryId -> category_id
createdAtFrom -> created_at_from
createdAtTo -> created_at_to
sortBy -> sort_by
sortDir -> sort_dir
```

Relation existence keys are normalized too:

```http
GET /posts?hasBlogComments=true
```

```text
hasBlogComments -> has_blog_comments
```

## Sorting

Sorting is ignored unless the requested field is listed in `sortableFields` or the `sortable` config key.

### `sort_by` + `sort_dir`

Use this style when your frontend has separate sort field and direction values.

```http
GET /posts?sort_by=views&sort_dir=desc
GET /posts?sort_by=views&sort_dir=DESC
GET /posts?sort_by=title&sort_dir=asc
```

| Parameter | Description |
| --- | --- |
| `sort_by` | Column to sort by. Must be listed in `sortableFields` or `sortable`. |
| `sort_dir` | Sort direction. Supports `asc`, `desc`, `ASC`, and `DESC`. Defaults to `asc` when invalid or missing. |

### `sort`

Use this style when you want compact API query parameters.

```http
GET /posts?sort=title
GET /posts?sort=-created_at
```

| Example | Result |
| --- | --- |
| `sort=title` | Sort by `title` ascending. |
| `sort=created_at` | Sort by `created_at` ascending. |
| `sort=-created_at` | Sort by `created_at` descending. |
| `sort=-views` | Sort by `views` descending. |

The minus prefix means descending order. Without the minus prefix, sorting is ascending.

If both `sort_by` and `sort` are present, `sort_by` takes priority.

Unknown or non-whitelisted sort fields are ignored:

```http
GET /posts?sort=password
GET /posts?sort_by=non_existing_column&sort_dir=desc
```

## Soft Deletes

These filters only apply to models that use Laravel's `SoftDeletes` trait.

```http
GET /posts?trashed=only
GET /posts?trashed=with
```

## Relation Filtering

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

If key normalization is enabled, camelCase relation existence keys also work:

```php
Post::filter($request->all(), [
    'normalize_keys' => true,
    'relation_exists' => ['blogComments'],
]);
```

```http
GET /posts?hasBlogComments=true
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

## Filter Aliases

Use `filterableMap` to expose public aliases without exposing internal column names.

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

A multi-column alias searches each mapped column with `LIKE` and groups them with `OR`:

```sql
WHERE (
    firstname LIKE '%john%'
    OR lastname LIKE '%john%'
)
```

## Custom Filters

### Model Method

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

### Filter Class

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

### Callback

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
- Avoid raw SQL in custom filters unless values are safely bound.
- Do not build filter or sort allowlists from request input.

## Testing

```bash
./vendor/bin/phpunit
```

## License

MIT
