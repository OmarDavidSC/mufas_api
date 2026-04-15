<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use SoftDeletes;
    protected $table = 'nodes';
    protected $fillable = [
        'id',
        'code',
        'latitude',
        'longitude',
        'reference',
        'district',
        'city',
        'status',
    ];
}
