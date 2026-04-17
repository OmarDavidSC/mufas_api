<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Fiber;
use App\Models\FiberAssignments;
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

            $query = FiberAssignments::with(['fiber', 'client'])
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'fiber_id' => $item->fiber_id,
                    'fiber_number' => $item->fiber_number,
                    'node_id' => $item->node_id,
                    'client_id' => $item->client_id,
                    'client_name' => $item->client->name,
                    'status' => $item->status,
                    'assigned_at' => 'assigned_at',
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
            $response['message'] = 'exito';
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

            $fiber_id = (int) trim($input['fiber_id']);
            $client_id = (int) trim($input['client_id']);

            if (empty($fiber_number) || empty($color) || empty($node_id)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $fiber = Fiber::find($fiber_id);
            if (!$fiber) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            if ($fiber->status != 'free') {
                $response['success'] = false;
                $response['message'] = "Hilo no esta disponible.";
                return $response;
            }


            $exists  = FiberAssignments::where('fiber_id', $fiber_id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->first();

            if ($exists) {
                $response['success'] = false;
                $response['message'] = "Hilo ya esta asignado.";
                return $response;
            }

            $assignment = new FiberAssignments();
            $assignment->fiber_id = $fiber_id;
            $assignment->client_id = $client_id;
            $assignment->status = 'active';
            $assignment->save();

            //actualizar estado del hilo(fibra)
            $fiber->status = 'occupied';
            $fiber->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $assignment;
            $response['message'] = 'El hilo fue asignado correctamente.';
        } catch (\Exception $e) {
            DB::rollBack();
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

            $assignment = FiberAssignments::find($id);
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

            $fiber = Fiber::find($assignment->fiber_id);


            //liberar asignación 
            $assignment->status = 'released';
            $assignment->save();

            //liberar hilo(fibra)
            if ($fiber) {
                $fiber->status = 'free';
                $fiber->save();
            }

            DB::commit();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = "Hilo fue liberado correctamente";
        } catch (\Exception $e) {
            DB::rollBack();
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
