# Filtering

Filtering is allowed only for fields listed in `$filterableFields` or mapped in `$filterableMap`.

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

## Exact Match

```http
GET /posts?status=published
GET /posts?category=tech
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

Use custom filters when each field needs partial matching with its own value.

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

```http
GET /posts?name=contoh&category=tech
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
