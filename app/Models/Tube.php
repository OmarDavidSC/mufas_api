<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tube extends Model
{
    use SoftDeletes;
    protected $table = 'tubes';
    protected $fillable = [
        'id',
        'fiber_id',
        'tube_number',
        'color',
    ];
}
