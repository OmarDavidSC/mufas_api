<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\FiberThread;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class FiberThreadDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $tube_id = isset($input['tube_id']) ? trim($input['tube_id']) : null;
            $status = isset($input['status']) ? trim($input['status']) : null;
            $search = isset($input['search']) ? trim($input['search']) : null;

            $query = FiberThread::whereNull('deleted_at');

            if (!empty($tube_id)) {
                $query->where('tube_id', $tube_id);
            }

            if (!empty($status)) {
                $query->where('status', $status);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('thread_number', 'like', "%$search%")
                        ->orWhere('color', 'like', "%$search%");
                });
            }

            $query->orderBy('thread_number', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tube_id' => $item->tube_id,
                    'thread_number' => $item->thread_number,
                    'color' => $item->color,
                    'status' => $item->status,
                    'datecreated_label' => FG::formatDateTimeHuman($item->created_at),
                    'dateupdated_label' => FG::formatDateTimeHuman($item->updated_at),
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

    public function bytube($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();
            $tube_id = $request->getAttribute('tube_id');

            if (empty($tube_id)) {
                $response['success'] = false;
                $response['message'] = "El ID del tubo es obligatorio.";
                return $response;
            }

            $items = FiberThread::whereNull('deleted_at')
                ->where('tube_id', $tube_id)
                ->orderBy('thread_number', 'asc')
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tube_id' => $item->tube_id,
                    'thread_number' => $item->thread_number,
                    'color' => $item->color,
                    'status' => $item->status,
                    'datecreated_label' => FG::formatDateTimeHuman($item->created_at),
                    'dateupdated_label' => FG::formatDateTimeHuman($item->updated_at),
                ];
            });

            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'hilos del tubo obtenidos correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function show($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();
            $id = $request->getAttribute('id');

            if (empty($id)) {
                $response['success'] = false;
                $response['message'] = "El ID del hilo es obligatorio.";
                return $response;
            }

            $thread = FiberThread::where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$thread) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            $data = [
                'id' => $thread->id,
                'tube_id' => $thread->tube_id,
                'thread_number' => $thread->thread_number,
                'color' => $thread->color,
                'status' => $thread->status,
                'created_at' => $thread->created_at,
                'updated_at' => $thread->updated_at,
                'datecreated_label' => FG::formatDateTimeHuman($thread->created_at),
                'dateupdated_label' => FG::formatDateTimeHuman($thread->updated_at),
            ];

            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'detalle del hilo obtenido correctamente';
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
            $id = $request->getAttribute('id');

            $thread = FiberThread::find($id);

            if (!$thread || $thread->deleted_at != null) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            $thread_number = (int) trim($input['thread_number']);
            $color = trim($input['color']);
            $status = trim($input['status']);

            if (empty($thread_number) || empty($color) || empty($status)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            //validar estados permitidos free, accupied, damaged
            $allowed_status = ['free', 'accupied', 'damaged'];
            if (!in_array($status, $allowed_status)) {
                $response['success'] = false;
                $response['message'] = "Estado no permitido. Los estados permitidos son: free, accupied, damaged.";
                return $response;
            }

            //validar numero repetido desde el mismo tubo
            $exists = FiberThread::where('tube_id', $thread->tube_id)
                ->where('thread_number', $thread_number)
                ->where('id', '!=', $thread->id)
                ->whereNull('deleted_at')
                ->first();

            if ($exists) {
                $response['success'] = false;
                $response['message'] = "Ya existe use número de hilo en este tubo.";
                return $response;
            }

            $thread->thread_number = $thread_number;
            $thread->color = $color;
            $thread->status = $status;
            $thread->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $thread;
            $response['message'] = 'Hilo registrado correctamente';
        } catch (\Exception $e) {
            DB::rollback();
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
            $user_id = Application::getItem('user_id');

            $thread = FiberThread::find($id);
            if (!$thread) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            if (empty($input['thread_number']) || empty($input['color'])) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            $thread->thread_number = $input['thread_number'];
            $thread->color = $input['color'];
            $thread->save();

            $response['success'] = true;
            $response['data'] = $thread;
            $response['message'] = "Hilo actualizado correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    //cambiar estado de hilo
    public function status($request)
    {
        $response = FG::responseDefault();

        try {

            $id = $request->getAttribute('id');
            $input = $request->getParsedBody();

            $status = trim($input['status']); // free | occupied | damaged
            if (empty($status)) {
                $response['success'] = false;
                $response['message'] = "Debe enviar el estado.";
                return $response;
            }

            if (!in_array($status, ['free', 'occupied', 'damaged'])) {
                $response['success'] = false;
                $response['message'] = "Estado no válido";
                return $response;
            }

            $thread = FiberThread::whereNull('deleted_at')
                ->find($id);
            if (!$thread) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado";
                return $response;
            }

            $thread->status = $status;
            $thread->save();

            $response['success'] = true;
            $response['data'] = $thread;
            $response['message'] = "Estado del hilo actualizado correctamente";
        } catch (\Exception $e) {

            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function trace($request)
    {
        $response = FG::responseDefault();

        try {

            $id = $request->getAttribute('id');
            $input = $request->getParsedBody();

            $thread = FiberThread::with([
                'tube.fiber.node',
                'assignments.client'
            ])->whereNull('deleted_at')
                ->find($id);

            if (!$thread) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado";
                return $response;
            }

            $client = null;

            if (isset($thread->assignments) && count($thread->assignments) > 0) {
                $last_assignment = $thread->assignments->last();

                if ($last_assignment && $last_assignment->client) {
                    $client = [
                        'id' => $last_assignment->client->id,
                        'name' => $last_assignment->client->name,
                        'phone' => $last_assignment->client->phone,
                        'address' => $last_assignment->client->address,
                    ];
                }
            }

            $data = [
                'thread' => [
                    'id' => $thread->id,
                    'thread_number' => $thread->thread_number,
                    'color' => $thread->color,
                    'status' => $thread->status
                ],
                'tube' => [
                    'id' => $thread->tube->id,
                    'tube_number' => $thread->tube->tube_number,
                    'color' => $thread->tube->color
                ],
                'fiber' => [
                    'id' => $thread->tube->fiber->id,
                    'cable_number' => $thread->tube->fiber->cable_number,
                    'color' => $thread->tube->fiber->color
                ],
                'node' => [
                    'id' => $thread->tube->fiber->node->id,
                    'name' => $thread->tube->fiber->node->name,
                    'code' => $thread->tube->fiber->node->code
                ],
                'client' => $client
            ];


            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = "Ruta del hilo obtenida correctamente";
        } catch (\Exception $e) {

            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
}
