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
                    'cable_number' => $item->cable_number,
                    'status' => $item->status,
                    'color' => $item->color,
                    'total_fibers' => $item->total_fibers,
                    'total_tubes' => $item->total_tubes,
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
                ->orderBy('cable_number', 'asc')
                ->get();

            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'cable_number' => $item->cable_number,
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
            $cable_number = trim($input['cable_number']);
            $color = trim($input['color']);
            $total_fibers = trim($input['total_fibers']);
            $tube_type = trim($input['tube_type']); // multi  | single


            if (empty($cable_number) || empty($color) || empty($node_id) || empty($total_fibers) || empty($tube_type)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $fiber = new Fiber();
            $fiber->cable_number = $cable_number;
            $fiber->color = $color;
            $fiber->node_id = $node_id;
            $fiber->total_fibers = $total_fibers;
            $fiber->tube_type = $tube_type;
            $fiber->save();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = 'Fibra creado correctamente';
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
                $response['message'] = "Fibra no encontrado.";
                return $response;
            }

            if (empty($input['cable_number']) || empty($input['color']) || empty($input['node_id']) || empty($input['total_fibers']) || empty($input['tube_type'])) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            $fiber->cable_number = $input['cable_number'];
            $fiber->color = $input['color'];
            $fiber->node_id = $input['node_id'];
            $fiber->total_fibers = $input['total_fibers'];
            $fiber->tube_type = $input['tube_type'];
            $fiber->save();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = "Fibra actualizada correctamente";
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
                $response['message'] = "Fibra no encontrado.";
                return $response;
            }

            $fiber->deleted_at = FG::getDateHour();
            $fiber->save();

            $response['success'] = true;
            $response['data'] = $fiber;
            $response['message'] = "Fibra fue eliminado correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
