<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Tube;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class TubeDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = Tube::whereNull('deleted_at')
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
                    'tuber_number' => $item->tuber_number,
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

            $items = Tube::whereNull('deleted_at')
                ->orderBy('tuber_number', 'asc')
                ->get();

            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tuber_number' => $item->tuber_number,
                    'color' => $item->color
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

            $fiber_id = trim($input['fiber_id']);
            $tube_number = trim($input['tube_number']);
            $color = trim($input['color']);


            if (empty($fiber_id) || empty($tube_number) || empty($color)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $tube = new Tube();
            $tube->fiber_id = $fiber_id;
            $tube->tuber_number = $tube_number;
            $tube->color = $color;
            $tube->save();

            $response['success'] = true;
            $response['data'] = $tube;
            $response['message'] = 'Tubo creado correctamente';
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

            $tube = Tube::find($id);
            if (!$tube) {
                $response['success'] = false;
                $response['message'] = "Tubo no encontrado.";
                return $response;
            }

            if (empty($input['tuber_number']) || empty($input['color'])) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            $tube->tuber_number = $input['tuber_number'];
            $tube->color = $input['color'];
            $tube->save();

            $response['success'] = true;
            $response['data'] = $tube;
            $response['message'] = "Tubo actualizado correctamente";
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

            $tube = Tube::find($id);
            if (!$tube) {
                $response['success'] = false;
                $response['message'] = "Tubo no encontrado.";
                return $response;
            }

            $tube->deleted_at = FG::getDateHour();
            $tube->save();

            $response['success'] = true;
            $response['data'] = $tube;
            $response['message'] = "Tubo fue eliminado correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
