<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NodeConnections extends Model
{
    use SoftDeletes;
    protected $table = 'node_connections';
    protected $fillable = [
        'id',
        'origin_node_id',
        'destination_node_id',
        'distance_meters',
        'description',
    ];

    public function origin()
    {
        return $this->belongsTo(Node::class, 'origin_node_id');
    }

    public function destination()
    {
        return $this->belongsTo(Node::class, 'destination_node_id');
    }
}
