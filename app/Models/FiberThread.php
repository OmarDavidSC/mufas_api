<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiberThread extends Model
{
    use SoftDeletes;
    protected $table = 'fiber_threads';
    protected $fillable = [
        'id',
        'tube_id',
        'thread_number',
        'color',
        'status',
    ];
}
