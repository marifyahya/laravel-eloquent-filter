# Searching

The `search` parameter runs a partial `LIKE` search across fields listed in `$searchableFields`.

```php
protected $searchableFields = [
    'title',
    'content',
];
```

```http
GET /posts?search=laravel
```

This produces a grouped search condition across the configured fields.

Use `search` when one keyword should be searched across multiple fields using `OR`.

Use custom filters when each column has its own value and should use field-level `LIKE` matching:

```php
public function filterName($query, $value): void
{
    $query->where('name', 'LIKE', "%{$value}%");
}
```

## Relation Search

Search fields may include relation columns using dot notation:

```php
protected $searchableFields = [
    'title',
    'author.name',
];
```

The package will use `orWhereHas()` for relation search fields.
