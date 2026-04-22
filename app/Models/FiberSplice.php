<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiberSplice extends Model
{
    use SoftDeletes;
    protected $table = 'fiber_splices';
    protected $fillable = [
        'id',
        'from_thread_id',
        'to_thread_id',
        'splice_type',
        'status',
    ];
}
