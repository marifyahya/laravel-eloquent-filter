<?php

namespace Marifyahya\EloquentFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $guarded = [];
    public $timestamps = false;
}
