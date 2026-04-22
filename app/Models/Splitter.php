<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Splitter extends Model
{
    use SoftDeletes;
    protected $table = 'splitters';
    protected $fillable = [
        'id',
        'node_id',
        'name',
        'input_thread_id',
        'type',
        'status',
    ];
}
