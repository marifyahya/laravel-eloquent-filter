<?php

namespace Marifyahya\EloquentFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Marifyahya\EloquentFilter\Traits\HasEloquentFilter;

class User extends Model
{
    use HasEloquentFilter;

    protected $table = 'users';
    protected $guarded = [];
    public $timestamps = false;

    protected $filterableFields = ['id', 'status'];
    protected $sortableFields = ['id', 'status', 'firstname', 'lastname', 'email'];
    protected $searchableFields = ['name', 'email'];
    protected $dateRangeFields = [];

    protected $filterableMap = [
        'name' => ['firstname', 'lastname'],
    ];

    public function filterName($query, $value)
    {
        $query->where(function ($q) use ($value) {
            $q->where('firstname', 'LIKE', "%{$value}%")
              ->orWhere('lastname', 'LIKE', "%{$value}%");
        });
    }
}
