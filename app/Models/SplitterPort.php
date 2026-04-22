<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SplitterPort extends Model
{
    use SoftDeletes;
    protected $table = 'splitter_ports';
    protected $fillable = [
        'id',
        'splitter_id',
        'port_number',
        'output_thread_id',
        'status',
    ];
}
