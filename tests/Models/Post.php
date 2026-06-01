<?php

namespace Marifyahya\EloquentFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Marifyahya\EloquentFilter\Traits\HasEloquentFilter;

class Post extends Model
{
    use HasEloquentFilter;

    protected $table = 'posts';
    protected $guarded = [];
    public $timestamps = false;

    protected $filterableFields = ['id', 'status', 'user_id'];
    protected $sortableFields = ['id', 'status', 'user_id', 'title', 'views', 'created_at'];
    protected $searchableFields = ['title', 'content'];
    protected $dateRangeFields = ['created_at'];

    protected $filterableMap = [
        'post_id' => 'id',
    ];

    public function filterStatus($query, $value)
    {
        if ($value === 'active_published') {
            $query->whereIn('status', ['active', 'published']);
        } else {
            $query->where('status', $value);
        }
    }
}
