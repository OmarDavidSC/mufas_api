<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiberAssignments extends Model
{
    use SoftDeletes;
    protected $table = 'fiber_assignments';
    protected $fillable = [
        'id',
        'splitter_port_id',
        'client_id',
        'assigned_at',
        'status',
    ];
}
