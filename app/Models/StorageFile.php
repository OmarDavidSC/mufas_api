<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class StorageFile extends Model
{

	protected $table = 'storage_files';

	protected $fillable = [
		'id',
		'name',
		'path',
		'type',
		'size_b',
		'size',
		'format',
		'bucket',
	];
}
