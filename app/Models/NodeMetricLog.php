<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as ModelM;
use Illuminate\Database\Capsule\Manager as DB;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class NodeMetricLog extends ModelM
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $collection = 'node_metric_logs';
    protected $connection = 'mongodb';
}
