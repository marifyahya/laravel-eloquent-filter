# Model Configuration

Model configuration controls which request parameters are allowed to affect the query.

```php
protected $filterableFields = [
    'id',
    'title',
    'status',
    'views',
    'is_featured',
    'published_at',
];

protected $sortableFields = [
    'id',
    'title',
    'status',
    'views',
    'published_at',
    'created_at',
];

protected $searchableFields = [
    'title',
    'content',
];

protected $dateRangeFields = [
    'created_at',
    'published_at',
];

protected $filterableMap = [
    'q' => ['title', 'content'],
    'blog_status' => 'status',
    'popular' => 'views',
    'featured_now' => 'is_featured',
];

protected $normalizeFilterKeys = true;
```

## Property Reference

| Property | Purpose |
| --- | --- |
| `$filterableFields` | Columns allowed for exact, operator, comma-separated, array, and null filters. |
| `$sortableFields` | Columns allowed for `sort` and `sort_by`. |
| `$searchableFields` | Columns searched by `search`. |
| `$dateRangeFields` | Date columns allowed for `{field}_from` and `{field}_to`. |
| `$filterableMap` | Public aliases mapped to real columns or multiple columns. |
| `$customFilters` | Custom filter classes or callbacks. |
| `$normalizeFilterKeys` | Converts camelCase request keys to snake_case. |

If `$sortableFields` is not set, sorting falls back to `$filterableFields`.

