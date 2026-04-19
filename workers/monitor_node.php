<?php

use App\Models\NodeMetricLog;
use App\Models\Node;
use App\Utilities\FG;

while (true) {

    $nodes = Node::whereNull('deleted_at')->get();
    foreach ($nodes as $node) {
        if (!$node->ip_address) {
            continue;
        }

        exec("ping -c 1 {$node->ip_address}", $output, $status);

        $isUp = ($status === 0);
        NodeMetricLog::create([
            'node_id' => $node->id,
            'ip_address' => $node->ip_address,
            'status' => $isUp ? 'up' : 'down',
            'traffic_in' => rand(100, 1000), // Simulación de tráfico entrante
            'traffic_out' => rand(100, 1000), // Simulación de tráfico saliente
            'timestamp' => FG::getDateHour(),
        ]);
    }   

    sleep(10); // Espera 10 segundos antes de la siguiente verificación
}
