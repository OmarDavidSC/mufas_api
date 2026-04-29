<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Fiber;
use App\Models\FiberAssignments;
use App\Models\Node;
use App\Models\NodeConnections;
use App\Models\NodeMetricLog;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class NodeConnectionDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();
            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = NodeConnections::with(['origin', 'destination'])
                ->orderBy('id', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'origin_node_id' => $item->origin_node_id,
                    'origin_node_code' => $item->origin->code ?? null,
                    'destination_node_id' => $item->destination_node_id,
                    'destination_node_code' => $item->destination->code ?? null,
                    'distance_meters' => $item->distance_meters,
                    'distance_km' => $item->distance_km,
                    'description' => $item->description,
                ];
            });

            $response['success'] = true;
            $response['data'] = [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'data' => $data
            ];
            $response['message'] = 'ok';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function store($request)
    {
        $response = FG::responseDefault();
        DB::beginTransaction();
        try {
            $input = $request->getParsedBody();
            $user_id = Application::getItem('user_id');

            $origin = $input['origin_node_id'] ?? null;
            $destination = $input['destination_node_id'] ?? null;
            $description = $input['description'] ?? null;


            if (empty($origin) || empty($destination)) {
                $response['success'] =  false;
                $response['message'] = "Debe seleccionar ambos nodos.";
                return $response;
            }

            if ($origin == $destination) {
                $response['success'] =  false;
                $response['message'] = "Los nodos de origen y destino no pueden ser iguales.";
                return $response;
            }

            $originNode = Node::find($origin);
            $destinationNode = Node::find($destination);

            if (!$originNode || !$destinationNode) {
                $response['success'] =  false;
                $response['message'] = "Uno de los nodos no existe.";
                return $response;
            }

            //validar duplicados 
            $existing = NodeConnections::where(function ($query) use ($origin, $destination) {
                $query->where('origin_node_id', $origin)
                    ->where('destination_node_id', $destination);
            })->orWhere(function ($query) use ($origin, $destination) {
                $query->where('origin_node_id', $destination)
                    ->where('destination_node_id', $origin);
            })->exists();

            if ($existing) {
                $response['success'] = false;
                $response['message'] = "La conexión entre estos nodos ya existe.";
                return $response;
            }

            //calcular distancia (HAVERSINE)
            $distanceKm = $this->calculateDistanceKm(
                $originNode->latitude,
                $originNode->longitude,
                $destinationNode->latitude,
                $destinationNode->longitude
            ); // Convertir a metros

            $distanceMeters = $distanceKm * 1000;

            $connection = new NodeConnections();
            $connection->origin_node_id = $origin;
            $connection->destination_node_id = $destination;
            $connection->distance_meters = round($distanceMeters, 2);
            $connection->distance_km = round($distanceKm, 2);
            $connection->description = $description;
            $connection->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $connection;
            $response['message'] = 'La conexión fue creada correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function update($request)
    {
        $response = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');
            $input = $request->getParsedBody();

            $connection = NodeConnections::find($id);
            if (!$connection) {
                $response['success'] = false;
                $response['message'] = "Conexión de estos nodos no fue encontrada.";
                return $response;
            }

            $distance = $input['distance_meters'] ?? null;
            $description = $input['description'] ?? null;

            $connection->distance_meters = $distance;
            $connection->description = $description;
            $connection->save();

            $response['success'] = true;
            $response['data'] = $connection;
            $response['message'] = "Conexión actualizada correctamente";
        } catch (\Exception $e) {
            DB::rollBack();
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function shortespath($request)
    {
        $response = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');
            $input = $request->getParsedBody();

            $start = $input['origin_node_id'];
            $end = $input['destination_node_id'];

            if (empty($start) || empty($end)) {
                $response['success'] = false;
                $response['message'] = "Debe proporcionar ambos nodos de origen y destino.";
                return $response;
            }

            $connections = NodeConnections::all();
            //construir grafo
            $graph = [];
            foreach ($connections as $conn) {
                $graph[$conn->origin_node_id][] = [
                    'node' => $conn->destination_node_id,
                    'distance' => $conn->distance_meters
                ];
                $graph[$conn->destination_node_id][] = [
                    'node' => $conn->origin_node_id,
                    'distance' => $conn->distance_meters
                ];
            }
            //dijkstra
            $distances = [];
            $previous = [];
            $queue = [];

            foreach ($graph as $node => $edges) {
                $distances[$node] = INF;
                $previous[$node] = null;
                $queue[$node] = true;
            }

            $distances[$start] = 0;
            while (!empty($queue)) {
                // nodo con menor distancia
                $minNode = null;
                foreach ($queue as $node => $val) {
                    if ($minNode === null || $distances[$node] < $distances[$minNode]) {
                        $minNode = $node;
                    }
                }
                if ($minNode === null) break;
                unset($queue[$minNode]);
                if (!isset($graph[$minNode])) continue;
                foreach ($graph[$minNode] as $neighbor) {
                    $alt = $distances[$minNode] + $neighbor['distance'];
                    if ($alt < $distances[$neighbor['node']]) {
                        $distances[$neighbor['node']] = $alt;
                        $previous[$neighbor['node']] = $minNode;
                    }
                }
            }
            //reconstruir camino
            $path = [];
            $current = $end;
            while ($current !== null) {
                array_unshift($path, $current);
                $current = $previous[$current];
            }

            if ($path[0] != $start) {
                $response['success'] = false;
                $response['message'] = "No existe un camino entre los nodos proporcionados.";
                return $response;
            }

            $totalDistanceMeters = $distances[$end];
            $totalDistanceKm = round($totalDistanceMeters / 1000, 2);
            $data = [
                'path' => $path,
                'total_distance_meters' => round($totalDistanceMeters, 2),
                'total_distance_km' => $totalDistanceKm
            ];

            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = "Camino más corto calculado correctamente.";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function remove($request)
    {
        $response = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');

            $connection = NodeConnections::find($id);
            if (!$connection) {
                $response['success'] = false;
                $response['message'] = "Conexión no encontrada.";
                return $response;
            }

            $connection->deleted_at = FG::getDateHour();
            $connection->save();

            $response['success'] = true;
            $response['data'] = $connection;
            $response['message'] = "Conexión fue eliminada correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function nstatus($request)
    {
        $response = FG::responseDefault();
        try {

            $nodes = Node::whereNull('deleted_at')->get();

            $metrics = NodeMetricLog::orderBy('timestamp', 'desc')->get();

            $latestByNode = [];
            foreach ($metrics as $item) {
                if (!isset($latestByNode[$item->node_id])) {
                    $latestByNode[$item->node_id] = $item;
                }
            }

            //unir datos 
            $data = $nodes->map(function ($node) use ($latestByNode) {
                $metric = $latestByNode[$node->id] ?? null;

                return [
                    'id' => $node->id,
                    'code' => $node->code,
                    'latitude' => $node->latitude,
                    'longitude' => $node->longitude,
                    'reference' => $node->reference,
                    'disctric'  => $node->district,
                    'city' => $node->city,

                    //estado en tiempo real
                    'status' => $metric ? $metric->status : 'unknown',
                    'traffic_in' => $metric ? $metric->traffic_in : null,
                    'traffic_out' => $metric ? $metric->traffic_out : null,
                    'timestamp' => $metric ? date('Y-m-d H:i:s', strtotime($metric->timestamp)) : null,
                ];
            });
            // $data = array_values($latestByNode);
            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = "successifully retrieved node status.";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function nhistory($request)
    {
        $response = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');

            $node_id = (int)$id;

            $items  = NodeMetricLog::where('node_id', $node_id)
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'node_id' => $item->node_id,
                    'status' => $item->status,
                    'traffic_in' => $item->traffic_in,
                    'traffic_out' => $item->traffic_out,
                    'timestamp' => date('Y-m-d H:i:s', strtotime($item->timestamp))
                ];
            });


            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = "successifully retrieved node history.";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function ncurrent($request)
    {
        $response = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');

            $node_id = (int)$id;

            $item  = NodeMetricLog::where('node_id', $node_id)
                ->orderBy('timestamp', 'desc')
                ->first();

            if (!$item) {
                $response['success'] = false;
                $response['message'] = "No se encontraron métricas para este nodo.";
                return $response;
            }

            $data = [
                'node_id' => $item->node_id,
                'status' => $item->status,
                'traffic_in' => $item->traffic_in,
                'traffic_out' => $item->traffic_out,
                'timestamp' => date('Y-m-d H:i:s', strtotime($item->timestamp))
            ];

            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = "successifully retrieved node current metrics.";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    private function calculateDistanceKm($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radio de la Tierra en kilómetros

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distancia en kilómetros
    }
}
