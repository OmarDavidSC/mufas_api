<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicationImages extends Model
{
    use SoftDeletes;
    protected $table = 'publication_images';
    protected $fillable = [
        'id',
        'publication_id',
        'image_id',
    ];
}
