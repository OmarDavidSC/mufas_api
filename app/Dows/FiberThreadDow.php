<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\FiberThread;
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

            $query = FiberThread::whereNull('deleted_at')
                ->orderBy('id', 'desc');

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

            $items = FiberThread::whereNull('deleted_at')
                ->orderBy('thread_number', 'asc')
                ->get();

            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'thread_number' => $item->thread_number,
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

            $tube_id = trim($input['tube_id']);
            $thread_number = trim($input['thread_number']);
            $color = trim($input['color']);


            if (empty($tube_id) || empty($thread_number) || empty($color)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $thread = new FiberThread();
            $thread->tube_id = $tube_id;
            $thread->thread_number = $thread_number;
            $thread->color = $color;
            $thread->save();

            $response['success'] = true;
            $response['data'] = $thread;
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

    public function remove($request)
    {
        $response = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');

            $thread = FiberThread::find($id);
            if (!$thread) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            $thread->deleted_at = FG::getDateHour();
            $thread->save();

            $response['success'] = true;
            $response['data'] = $thread;
            $response['message'] = "Hilo fue eliminado correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
