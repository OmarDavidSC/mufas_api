<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use SoftDeletes;
    protected $table = 'publication';
    protected $fillable = [
        'id',
        'user_id',
        'template_id',
        'description',
        'status',
    ];
}
