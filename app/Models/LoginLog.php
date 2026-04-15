<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as ModelM;
use Illuminate\Database\Capsule\Manager as DB;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class LoginLog extends ModelM
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $collection = 'login_admin';
    protected $connection = 'mongodb';
}
