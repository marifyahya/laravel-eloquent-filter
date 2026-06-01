# Relation Filtering

The package supports basic relation filtering through `whereHas()`, `has()`, and `doesntHave()`.

## Relation Existence

```php
Post::filter($request->all(), [
    'relation_exists' => ['comments', 'likes'],
]);
```

```http
GET /posts?has_comments=true
GET /posts?has_comments=false
```

- `has_comments=true` applies `has('comments')`.
- `has_comments=false` applies `doesntHave('comments')`.

If key normalization is enabled, camelCase relation existence keys are normalized before matching:

```php
Post::filter($request->all(), [
    'normalize_keys' => true,
    'relation_exists' => ['blogComments'],
]);
```

```http
GET /posts?hasBlogComments=true
```

This applies `has('blogComments')`.

## Relation Fields

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

## Limitations

- Nested relation filtering such as `author.company.status` is not supported yet.
- Relation field operators such as `author.age=>30` are not supported yet.
- Sorting by relation columns is not supported yet.
