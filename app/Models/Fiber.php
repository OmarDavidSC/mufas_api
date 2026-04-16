<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fiber extends Model
{
    use SoftDeletes;
    protected $table = 'fibers';
    protected $fillable = [
        'id',
        'node_id',
        'fiber_number',
        'status',
        'color',
    ];
}
