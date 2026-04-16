<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $table = 'clients';
    protected $fillable = [
        'id',
        'dni',
        'name',
        'document_number',
        'phone',
        'address',
        'district',
        'city',
        'latitude',
        'longitude',
        'status',
    ];
}
