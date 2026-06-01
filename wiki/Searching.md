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

## Relation Search

Search fields may include relation columns using dot notation:

```php
protected $searchableFields = [
    'title',
    'author.name',
];
```

The package will use `orWhereHas()` for relation search fields.

