<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\FiberThread;
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
        DB::beginTransaction();
        try {
            $input = $request->getParsedBody();
            $user_id = Application::getItem('user_id');

            $fiber_id = trim($input['fiber_id']);
            $tube_number = trim($input['tube_number']);
            $color = trim($input['color']);
            $total_threads = isset($input['total_threads']) ? (int)$input['total_threads'] : 6;


            if (empty($fiber_id) || empty($tube_number) || empty($color)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $exits = Tube::where('fiber_id', $fiber_id)
                ->where('tuber_number', $tube_number)
                ->whereNull('deleted_at')
                ->first();

            if ($exits) {
                $response['success'] = false;
                $response['message'] = "El número de tubo ya existe para esta fibra.";
                return $response;
            }

            $tube = new Tube();
            $tube->fiber_id = $fiber_id;
            $tube->tuber_number = $tube_number;
            $tube->color = $color;
            $tube->save();


            $colors = ['azul', 'naranja', 'verde', 'marron', 'gris', 'blanco'];

            for ($i = 1; $i <= $total_threads; $i++) {
                $thread = new FiberThread();
                $thread->tube_id = $tube->id;
                $thread->thread_number = $i;
                $thread->color = $colors[($i - 1) % count($colors)];
                $thread->status = 'free';
                $thread->save();
            }

            DB::commit();
            $response['success'] = true;
            $response['data'] = $tube;
            $response['message'] = 'Tubo creado correctamente';
        } catch (\Exception $e) {
            DB::rollback();
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function update($request)
    {
        $response = FG::responseDefault();
        DB::beginTransaction();
        try {
            $id = $request->getAttribute('id');
            $input = $request->getParsedBody();
            $user_id = Application::getItem('user_id');

            $tube_number = trim($input['tube_number']);
            $color = trim($input['color']);
            $total_threads = isset($input['total_threads']) ? (int)$input['total_threads'] : 6;

            $tube = Tube::find($id);
            if (!$tube) {
                $response['success'] = false;
                $response['message'] = "Tubo no encontrado.";
                return $response;
            }

            if (empty($tube_number) || empty($color)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            $exists = Tube::where('fiber_id', $tube->fiber_id)
                ->where('tube_number', $tube_number)
                ->where('id', '!=', $tube->id)
                ->whereNull('deleted_at')
                ->first();

            if ($exists) {
                throw new \Exception("Ya existe ese número de tubo en esta fibra");
            }

            $tube->tuber_number = $tube_number;
            $tube->color = $color;
            $tube->save();

            $this->syncThreads($tube->id, $total_threads);
            DB::commit();

            $response['success'] = true;
            $response['data'] = $tube;
            $response['message'] = "Tubo actualizado correctamente";
        } catch (\Exception $e) {
            DB::rollback();
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

    private function syncThreads($tube_id, $total_threads)
    {
        $colors = ['azul', 'naranja', 'verde', 'marron', 'gris', 'blanco'];

        $currentThreads = FiberThread::where('tube_id', $tube_id)
            ->whereNull('deleted_at')
            ->get();

        $currentCount = $currentThreads->count();

        //aumentar hilos
        if ($total_threads > $currentCount) {
            for ($i = $currentCount + 1; $i <= $total_threads; $i++) {
                $thread = new FiberThread();
                $thread->tube_id = $tube_id;
                $thread->thread_number = $i;
                $thread->color = $colors[($i - 1) % count($colors)];
                $thread->status = 'free';
                $thread->save();
            }
        }

        //dismunuir hilos
        if ($total_threads < $currentCount) {

            $threadsToDelete = FiberThread::where('tube_id', $tube_id)
                ->where('thread_number', '>', $total_threads)
                ->whereNull('deleted_at')
                ->get();

            foreach ($threadsToDelete as $thread) {
                if ($thread->status === 'occupied') {
                    throw new \Exception("No puedes eliminar hilos ocupados");
                }
                $thread->deleted_at = FG::getDateHour();
                $thread->save();
            }
        }
    }
}
