<?php

namespace App\Dows;

use App\Models\Splitter;
use App\Models\SplitterPort;
use App\Models\FiberThread;
use App\Utilities\FG;
use Illuminate\Database\Capsule\Manager as DB;

class SplitterDow
{
    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = Splitter::from('splitters as s')
                ->leftJoin('splitter_ports as sp', function ($join) {
                    $join->on('sp.splitter_id', '=', 's.id')
                        ->whereNull('sp.deleted_at');
                })
                ->whereNull('s.deleted_at')
                ->select(
                    's.id',
                    's.node_id',
                    's.name',
                    's.input_thread_id',
                    's.type',
                    's.status',
                    's.created_at',
                    DB::raw('COUNT(sp.id) as ports_total'),
                    DB::raw("SUM(CASE WHEN sp.status = 'occupied' THEN 1 ELSE 0 END) as ports_used")
                )
                ->groupBy(
                    's.id',
                    's.node_id',
                    's.name',
                    's.input_thread_id',
                    's.type',
                    's.status',
                    's.created_at'
                )
                ->orderBy('s.id', 'desc');

            $total = Splitter::whereNull('deleted_at')->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {

                $portsTotal = (int)$item->ports_total;
                $portsUsed = (int)$item->ports_used;

                return [
                    'id' => $item->id,
                    'node_id' => $item->node_id,
                    'name' => $item->name,
                    'input_thread_id' => $item->input_thread_id,
                    'type' => $item->type,
                    'status' => $item->status,
                    'ports_total' => $portsTotal,
                    'ports_used' => $portsUsed,
                    'ports_free' => $portsTotal - $portsUsed,
                    'datecreated_label' => FG::formatDateTimeHuman($item->created_at)
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

            $response['message'] = 'Lista de splitters';
        } catch (\Exception $e) {

            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function show($request)
    {
        $response = FG::responseDefault();

        try {

            $id = $request->getAttribute('id');

            $item = Splitter::find($id);

            if (!$item) {
                throw new \Exception("Splitter no encontrado");
            }

            $ports = SplitterPort::where('splitter_id', $item->id)
                ->whereNull('deleted_at')
                ->orderBy('port_number', 'asc')
                ->get();

            $response['success'] = true;
            $response['data'] = [
                'splitter' => $item,
                'ports' => $ports
            ];
            $response['message'] = 'Detalle splitter';
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

            $node_id = trim($input['node_id']);
            $name = trim($input['name']);
            $input_thread_id = trim($input['input_thread_id']);
            $type = trim($input['type']);

            if (
                empty($node_id) ||
                empty($name) ||
                empty($input_thread_id) ||
                empty($type)
            ) {
                throw new \Exception("Todos los campos son obligatorios");
            }

            $thread = FiberThread::find($input_thread_id);

            if (!$thread) {
                throw new \Exception("Hilo de entrada no encontrado. Porfavor verifica el ID del hilo.");
            }

            if ($thread->status == 'occupied') {
                throw new \Exception("Ese hilo ya está ocupado");
            }

            $splitter = new Splitter();
            $splitter->node_id = $node_id;
            $splitter->name = $name;
            $splitter->input_thread_id = $input_thread_id;
            $splitter->type = $type;
            $splitter->status = 'active';
            $splitter->save();

            $ports = $this->getPortsByType($type);

            for ($i = 1; $i <= $ports; $i++) {

                $port = new SplitterPort();
                $port->splitter_id = $splitter->id;
                $port->port_number = $i;
                $port->status = 'free';
                $port->save();
            }

            $thread->status = 'occupied';
            $thread->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $splitter;
            $response['message'] = 'Splitter creado correctamente';
        } catch (\Exception $e) {

             DB::rollBack();

            $response['success'] = false;
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

            $splitter = Splitter::find($id);

            if (!$splitter) {
                throw new \Exception("Splitter no encontrado");
            }

            $splitter->name = trim($input['name']);
            $splitter->status = trim($input['status']);
            $splitter->save();

            $response['success'] = true;
            $response['data'] = $splitter;
            $response['message'] = 'Splitter actualizado';
        } catch (\Exception $e) {

            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function remove($request)
    {
        $response = FG::responseDefault();

        DB::beginTransaction();

        try {

            $id = $request->getAttribute('id');

            $splitter = Splitter::find($id);

            if (!$splitter) {
                throw new \Exception("Splitter no encontrado");
            }

            $usedPorts = SplitterPort::where('splitter_id', $id)
                ->where('status', 'occupied')
                ->whereNull('deleted_at')
                ->count();

            if ($usedPorts > 0) {
                throw new \Exception("No puedes eliminar splitter con puertos ocupados");
            }

            SplitterPort::where('splitter_id', $id)
                ->update([
                    'deleted_at' => FG::getDateHour()
                ]);

            $thread = FiberThread::find($splitter->input_thread_id);

            if ($thread) {
                $thread->status = 'free';
                $thread->save();
            }

            $splitter->deleted_at = FG::getDateHour();
            $splitter->save();

            DB::commit();

            $response['success'] = true;
            $response['message'] = 'Splitter eliminado correctamente';
        } catch (\Exception $e) {

            DB::rollBack();

            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function ports($request)
    {
        $response = FG::responseDefault();

        try {

            $id = $request->getAttribute('id');

            $ports = SplitterPort::where('splitter_id', $id)
                ->whereNull('deleted_at')
                ->orderBy('port_number', 'asc')
                ->get();

            $response['success'] = true;
            $response['data'] = $ports;
            $response['message'] = 'Puertos del splitter';
        } catch (\Exception $e) {

            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    private function getPortsByType($type)
    {
        switch ($type) {
            case '1x2':
                return 2;

            case '1x4':
                return 4;

            case '1x8':
                return 8;

            case '1x16':
                return 16;

            case '1x32':
                return 32;

            case '1x64':
                return 64;

            default:
                throw new \Exception("Tipo de splitter inválido");
        }
    }
}
