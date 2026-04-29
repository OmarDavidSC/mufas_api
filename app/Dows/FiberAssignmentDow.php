<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Client;
use App\Models\Fiber;
use App\Models\FiberAssignments;
use App\Models\SplitterPort;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class FiberAssignmentDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = FiberAssignments::from('fiber_assignments as fa')
                ->leftJoin('clients as c', 'c.id', '=', 'fa.client_id')
                ->leftJoin('splitter_ports as sp', 'sp.id', '=', 'fa.splitter_port_id')
                ->leftJoin('splitters as s', 's.id', '=', 'sp.splitter_id')
                ->whereNull('fa.deleted_at');

            $total = $query->count();

            $items = $query
                ->select(
                    'fa.id',
                    'fa.client_id',
                    'fa.splitter_port_id',
                    'fa.status',
                    'fa.assigned_at',
                    'c.name as client_name',
                    'c.phone as client_phone',
                    's.name as splitter_name',
                    'sp.port_number'
                )
                ->orderBy('fa.id', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'client_id' => $item->client_id,
                    'client_name' => $item->client_name,
                    'client_phone' => $item->client_phone,
                    'splitter_port_id' => $item->splitter_port_id,
                    'splitter_name' => $item->splitter_name,
                    'port_number' => $item->port_number,
                    'status' => $item->status,
                    'assigned_at_label' => FG::formatDateTimeHuman($item->assigned_at)
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
            $response['message'] = 'successfully';
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

            $client_id = (int) trim($input['client_id']);
            $splitter_port_id = (int) trim($input['splitter_port_id']);

            if (empty($client_id) || empty($splitter_port_id)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $client = Client::where('id', $client_id)->whereNull('deleted_at')->first();

            if (!$client) {
                $response['success'] = false;
                $response['message'] = "Cliente no encontrado.";
                return $response;
            }

            $port = SplitterPort::where('id', $splitter_port_id)->whereNull('deleted_at')->first();

            if (!$port) {
                $response['success'] = false;
                $response['message'] = "Puerto no encontrado.";
                return $response;
            }

            if ($port->status === 'occupied') {
                $response['success'] = false;
                $response['message'] = "Puerto ya esta ocupado.";
                return $response;
            }

            $exists = FiberAssignments::where('client_id', $client_id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->first();

            if ($exists) {
                $response['success'] = false;
                $response['message'] = "El cliente ya tiene una asignación activa.";
                return $response;
            }

            $assignment = new FiberAssignments();
            $assignment->client_id = $client_id;
            $assignment->splitter_port_id = $splitter_port_id;
            $assignment->assigned_at = FG::getDateHour();
            $assignment->status = 'active';
            $assignment->save();

            //actualizar estado del hilo(fibra)
            $port->status = 'occupied';
            $port->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $assignment;
            $response['message'] = 'Cliente asignado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function show($request)
    {
        $response = FG::responseDefault();

        try {

            $id = $request->getAttribute('id');

            $item = FiberAssignments::from('fiber_assignments as fa')
                ->leftJoin('clients as c', 'c.id', '=', 'fa.client_id')
                ->leftJoin('splitter_ports as sp', 'sp.id', '=', 'fa.splitter_port_id')
                ->leftJoin('splitters as s', 's.id', '=', 'sp.splitter_id')
                ->where('fa.id', $id)
                ->whereNull('fa.deleted_at')
                ->select(
                    'fa.id',
                    'fa.client_id',
                    'fa.splitter_port_id',
                    'fa.status',
                    'fa.assigned_at',
                    'c.name as client_name',
                    'c.phone as client_phone',
                    'c.address as client_address',
                    's.id as splitter_id',
                    's.name as splitter_name',
                    's.type as splitter_type',
                    'sp.port_number'
                )
                ->first();

            if (!$item) {
                $response['success'] = false;
                $response['message'] = "Asignación no encontrada.";
                return $response;
            }

            $response['success'] = true;
            $response['data'] = [
                'id' => $item->id,
                'client_id' => $item->client_id,
                'client_name' => $item->client_name,
                'client_phone' => $item->client_phone,
                'client_address' => $item->client_address,
                'splitter_id' => $item->splitter_id,
                'splitter_name' => $item->splitter_name,
                'splitter_type' => $item->splitter_type,
                'splitter_port_id' => $item->splitter_port_id,
                'port_number' => $item->port_number,
                'status' => $item->status,
                'assigned_at_label' => FG::formatDateTimeHuman($item->assigned_at)
            ];

            $response['message'] = 'Detalle de asignación';
        } catch (\Exception $e) {

            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function release($request)
    {
        $response = FG::responseDefault();
        DB::beginTransaction();
        try {
            $id = $request->getAttribute('id');

            $assignment = FiberAssignments::where('id', $id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->first();

            if (!$assignment) {
                $response['success'] = false;
                $response['message'] = "Asignación no encontrada.";
                return $response;
            }

            if ($assignment->status != 'active') {
                $response['success'] = false;
                $response['message'] = "Asignación no esta activa.";
                return $response;
            }

            $port = SplitterPort::where('id', $assignment->splitter_port_id)
                ->whereNull('deleted_at')
                ->first();

            if ($port) {
                $port->status = 'free';
                $port->save();
            }


            //liberar asignación 
            $assignment->status = 'released';
            $assignment->deleted_at = FG::getDateHour();
            $assignment->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $assignment;
            $response['message'] = "Asignación liberada correctamente.";
        } catch (\Exception $e) {
            DB::rollBack();
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function client($request)
    {
        $response = FG::responseDefault();

        try {

            $client_id = $request->getAttribute('client_id');

            $items = FiberAssignments::from('fiber_assignments as fa')
                ->leftJoin('splitter_ports as sp', 'sp.id', '=', 'fa.splitter_port_id')
                ->leftJoin('splitters as s', 's.id', '=', 'sp.splitter_id')
                ->where('fa.client_id', $client_id)
                ->whereNull('fa.deleted_at')
                ->select(
                    'fa.id',
                    'fa.status',
                    'fa.assigned_at',
                    'fa.splitter_port_id',
                    's.name as splitter_name',
                    's.type as splitter_type',
                    'sp.port_number'
                )
                ->orderBy('fa.id', 'desc')
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'splitter_port_id' => $item->splitter_port_id,
                    'splitter_name' => $item->splitter_name,
                    'splitter_type' => $item->splitter_type,
                    'port_number' => $item->port_number,
                    'status' => $item->status,
                    'assigned_at_label' => FG::formatDateTimeHuman($item->assigned_at)
                ];
            });

            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Asignaciones del cliente';
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

            $assignment = FiberAssignments::find($id);
            if (!$assignment) {
                $response['success'] = false;
                $response['message'] = "Asignación no encontrada.";
                return $response;
            }

            $assignment->deleted_at = FG::getDateHour();
            $assignment->save();

            $response['success'] = true;
            $response['data'] = $assignment;
            $response['message'] = "Asignación fue eliminada correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
