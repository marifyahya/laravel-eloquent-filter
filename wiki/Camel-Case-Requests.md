# Camel Case Requests

Many frontend applications send camelCase query parameters. Laravel database columns are usually snake_case.

Enable key normalization to convert request keys to snake_case before filtering.

## Model Level

```php
class Post extends Model
{
    use HasEloquentFilter;

    protected $normalizeFilterKeys = true;
}
```

## Query Level

```php
Post::filter($request->all(), [
    'normalize_keys' => true,
]);
```

## Example

```http
GET /posts?categoryId=2&createdAtFrom=2024-01-01&sortBy=created_at&sortDir=desc
```

is normalized to:

```text
categoryId -> category_id
createdAtFrom -> created_at_from
sortBy -> sort_by
sortDir -> sort_dir
```

## Override Model Default

If the model enables normalization, a single query can disable it:

```php
Post::filter($request->all(), [
    'normalize_keys' => false,
]);
```

