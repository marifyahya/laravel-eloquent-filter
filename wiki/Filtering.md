# Filtering

Filtering is allowed only for fields listed in `$filterableFields` or mapped in `$filterableMap`.

## Exact Match

```http
GET /posts?status=published
```

## Multiple Values

Comma-separated values use `WHERE IN`:

```http
GET /posts?status=active,pending,draft
```

Arrays also use `WHERE IN`:

```http
GET /posts?status[]=active&status[]=pending
```

Prefix comma-separated values with `!` to use `WHERE NOT IN`:

```http
GET /posts?status=!draft,archived
```

## NULL / NOT NULL

```http
GET /posts?deleted_at=null
GET /posts?deleted_at=!null
```

## Operators

```http
GET /posts?views=>100
GET /posts?views=<50
GET /posts?views=>=10
GET /posts?views=<=100
GET /posts?views=!=0
GET /posts?title=likeLaravel
```

## Between

```http
GET /posts?views=<>10,100
```

## Aliases

```php
protected $filterableMap = [
    'blog_status' => 'status',
    'q' => ['title', 'content'],
];
```

```http
GET /posts?blog_status=published
GET /posts?q=laravel
```

