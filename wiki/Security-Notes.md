# Security Notes

Laravel Eloquent Filter is designed around developer-controlled whitelists.

## Safe Defaults

- Unknown filters are ignored.
- Unknown sort fields are ignored.
- Sorting requires an allowlist.
- Filter values are passed through Eloquent query builder methods.

## What Developers Should Avoid

Do not build allowlists from user input:

```php
// Avoid this
'filterable' => $request->input('fields')
```

Do not expose sensitive columns:

```php
protected $filterableFields = [
    'password',
    'remember_token',
];
```

Be careful with custom filters that use raw SQL:

```php
// Avoid interpolating user input into raw SQL
$query->whereRaw("title LIKE '%{$value}%'");
```

Prefer bound query builder methods:

```php
$query->where('title', 'LIKE', "%{$value}%");
```

## Recommended Pattern

Define allowed fields directly in the model:

```php
protected $filterableFields = ['id', 'status'];
protected $sortableFields = ['created_at'];
protected $searchableFields = ['title', 'content'];
```

