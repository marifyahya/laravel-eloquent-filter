# Sorting

Sorting is allowed only for fields listed in `$sortableFields` or the `sortable` config key.

```php
protected $sortableFields = [
    'id',
    'title',
    'views',
    'created_at',
];
```

## `sort_by` and `sort_dir`

```http
GET /posts?sort_by=views&sort_dir=desc
GET /posts?sort_by=title&sort_dir=asc
```

`sort_dir` accepts `asc`, `desc`, `ASC`, and `DESC`.

## Compact `sort`

```http
GET /posts?sort=title
GET /posts?sort=-created_at
```

- `sort=title` sorts ascending.
- `sort=-created_at` sorts descending.

## Unknown Sort Fields

Unknown or non-whitelisted sort fields are ignored.

Relation sorting is not supported yet.

