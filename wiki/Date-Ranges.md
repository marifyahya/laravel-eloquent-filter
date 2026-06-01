# Date Ranges

Date range filters are enabled only for fields listed in `$dateRangeFields` or the `date_ranges` config key.

```php
protected $dateRangeFields = [
    'created_at',
    'published_at',
];
```

## From / To

```http
GET /posts?created_at_from=2024-01-01&created_at_to=2024-12-31
```

This applies:

```php
whereDate('created_at', '>=', '2024-01-01')
whereDate('created_at', '<=', '2024-12-31')
```

## With Camel Case Normalization

If `$normalizeFilterKeys = true`, this also works:

```http
GET /posts?createdAtFrom=2024-01-01&createdAtTo=2024-12-31
```

