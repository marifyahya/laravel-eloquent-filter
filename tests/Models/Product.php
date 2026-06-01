<?php

namespace Marifyahya\EloquentFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Marifyahya\EloquentFilter\Traits\HasEloquentFilter;

class Product extends Model
{
    use HasEloquentFilter, SoftDeletes;

    protected $table = 'products';
    protected $guarded = [];
    public $timestamps = false;

    protected $filterableFields = ['id', 'status', 'category'];
    protected $sortableFields = ['id', 'status', 'category', 'name', 'created_at'];
    protected $searchableFields = ['name'];
    protected $dateRangeFields = ['created_at'];
}
