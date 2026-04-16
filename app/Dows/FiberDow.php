<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Fiber;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class FiberDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = Fiber::whereNull('deleted_at')
                ->orderBy('id', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'fiber_number' => $item->fiber_number,
                    'status' => $item->status,
                    'color' => $item->color,
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
            $response['message'] = 'exito';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function adm($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();

            $items = Fiber::whereNull('deleted_at')
                ->orderBy('fiber_number', 'asc')
                ->get();

            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'fiber_number' => $item->fiber_number,
                ];
            });

            $response['success'] = true;
            $response['data'] = $items;
            $response['message'] = 'adm';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function store($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $user_id = Application::getItem('user_id');

            $node_id = trim($input['node_id']);
            $fiber_number = trim($input['fiber_number']);
            $color = trim($input['color']);


            if (empty($fiber_number) || empty($color) || empty($node_id)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $fiber = new Fiber();
            $fiber->fiber_number = $fiber_number;
            $fiber->color = $color;
            $fiber->node_id = $node_id;
            $fiber->save();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = 'Hilo creado correctamente';
        } catch (\Exception $e) {
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

            $fiber = Fiber::find($id);
            if (!$fiber) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            if (empty($input['fiber_number']) || empty($input['color'])) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            $fiber->fiber_number = $input['fiber_number'];
            $fiber->color = $input['color'];
            $fiber->node_id = $input['node_id'];
            $fiber->save();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = "Hilo actualizado correctamente";
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

            $fiber = Fiber::find($id);
            if (!$fiber) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            $fiber->deleted_at = FG::getDateHour();
            $fiber->save();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = "Hilo fue eliminado correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
