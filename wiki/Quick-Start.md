# Quick Start

Add the `HasEloquentFilter` trait to your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Marifyahya\EloquentFilter\Traits\HasEloquentFilter;

class Post extends Model
{
    use HasEloquentFilter;

    protected $filterableFields = ['id', 'status', 'category_id'];
    protected $sortableFields = ['id', 'title', 'created_at'];
    protected $searchableFields = ['title', 'content'];
    protected $dateRangeFields = ['created_at', 'published_at'];
}
```

Use `filter()` in a controller:

```php
use App\Models\Post;
use Illuminate\Http\Request;

public function index(Request $request)
{
    return Post::filter($request->all())->paginate(15);
}
```

Example request:

```http
GET /posts?status=published&search=laravel&sort=-created_at
```

