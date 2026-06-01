# Custom Filters

Custom filters let you define behavior that is more complex than the built-in filter operators.

## Model Method

Model methods named `filter{Field}` take priority over the default filter behavior.

```php
class Post extends Model
{
    public function filterStatus($query, $value): void
    {
        if ($value === 'published,reviewed') {
            $query->whereIn('status', ['published', 'reviewed'])
                ->where('approved', true);

            return;
        }

        $query->where('status', $value);
    }
}
```

```http
GET /posts?status=published,reviewed
```

## Filter Class

```php
class PopularFilter
{
    public function apply($query, $value): void
    {
        $query->where('views', '>', 1000);
    }
}
```

```php
Post::filter($request->all(), [
    'custom_filters' => [
        'popular' => \App\Filters\PopularFilter::class,
    ],
]);
```

## Callback

```php
Post::filter($request->all(), [
    'custom_filters' => [
        'title' => fn($query, $value) => $query->where('title', 'LIKE', "%{$value}%"),
    ],
]);
```

Avoid raw SQL in custom filters unless the values are safely bound.

