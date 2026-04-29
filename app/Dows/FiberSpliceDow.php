<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Client;
use App\Models\Fiber;
use App\Models\FiberAssignments;
use App\Models\FiberSplice;
use App\Models\FiberThread;
use App\Models\SplitterPort;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class FiberSpliceDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = FiberSplice::from('fiber_splices as fs')
                ->leftJoin('fiber_threads as ft1', 'ft1.id', '=', 'fs.from_thread_id')
                ->leftJoin('fiber_threads as ft2', 'ft2.id', '=', 'fs.to_thread_id')
                ->leftJoin('tube as t1', 't1.id', '=', 'ft1.tube_id')
                ->leftJoin('tube as t2', 't2.id', '=', 'ft2.tube_id')
                ->whereNull('fs.deleted_at')
                ->orderBy('fs.id', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get([
                    'fs.id',
                    'fs.from_thread_id',
                    'fs.to_thread_id',
                    'fs.splice_type',
                    'fs.status',
                    'fs.created_at',

                    'ft1.thread_number as from_thread_number',
                    'ft1.color as from_thread_color',

                    'ft2.thread_number as to_thread_number',
                    'ft2.color as to_thread_color',

                    't1.tube_number as from_tube_number',
                    't2.tube_number as to_tube_number'
                ]);

            $data = $items->map(function ($item) {

                return [
                    'id' => $item->id,

                    'from_thread_id' => $item->from_thread_id,
                    'from_tube_number' => $item->from_tube_number,
                    'from_thread_number' => $item->from_thread_number,
                    'from_thread_color' => $item->from_thread_color,

                    'to_thread_id' => $item->to_thread_id,
                    'to_tube_number' => $item->to_tube_number,
                    'to_thread_number' => $item->to_thread_number,
                    'to_thread_color' => $item->to_thread_color,

                    'splice_type' => $item->splice_type,
                    'status' => $item->status,

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
            $response['message'] = 'Lista de empalmen';
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

            $from_thread_id = trim($input['from_thread_id']);
            $to_thread_id = trim($input['to_thread_id']);
            $splice_type = isset($input['splice_type']) ? trim($input['splice_type']) : 'fusion';

            if (empty($from_thread_id) || empty($to_thread_id)) {
                $response['success'] = false;
                $response['message'] = 'Hilos de origen y destino son requeridos.';
                return $response;
            }

            if ($from_thread_id == $to_thread_id) {
                $response['success'] = false;
                $response['message'] = 'No puedes empalmar el mismo hilo.';
                return $response;
            }

            $from_thread = FiberThread::where('id', $from_thread_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$from_thread) {
                $response['success'] = false;
                $response['message'] = 'Hilo de origen no encontrado.';
                return $response;
            }

            $to_thread = FiberThread::where('id', $to_thread_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$to_thread) {
                $response['success'] = false;
                $response['message'] = 'Hilo de destino no encontrado.';
                return $response;
            }

            $exists = FiberSplice::where(function ($q) use ($from_thread_id, $to_thread_id) {
                $q->where('from_thread_id', $from_thread_id)
                    ->where('to_thread_id', $to_thread_id);
            })
                ->orWhere(function ($q) use ($from_thread_id, $to_thread_id) {
                    $q->where('from_thread_id', $to_thread_id)
                        ->where('to_thread_id', $from_thread_id);
                })
                ->whereNull('deleted_at')
                ->first();

            if ($exists) {
                throw new \Exception("Ese empalme ya existe");
            }

            $splice = new FiberSplice();
            $splice->from_thread_id = $from_thread_id;
            $splice->to_thread_id = $to_thread_id;
            $splice->splice_type = $splice_type;
            $splice->status = 'active';
            $splice->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $splice;
            $response['message'] = 'Empalme creado correctamente.';
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

            $item = FiberSplice::from('fiber_splices as fs')
                ->leftJoin('fiber_threads as ft1', 'ft1.id', '=', 'fs.from_thread_id')
                ->leftJoin('fiber_threads as ft2', 'ft2.id', '=', 'fs.to_thread_id')
                ->leftJoin('tubes as t1', 't1.id', '=', 'ft1.tube_id')
                ->leftJoin('tubes as t2', 't2.id', '=', 'ft2.tube_id')
                ->where('fs.id', $id)
                ->whereNull('fs.deleted_at')
                ->first([
                    'fs.id',
                    'fs.from_thread_id',
                    'fs.to_thread_id',
                    'fs.splice_type',
                    'fs.status',
                    'fs.created_at',

                    'ft1.thread_number as from_thread_number',
                    'ft1.color as from_thread_color',

                    'ft2.thread_number as to_thread_number',
                    'ft2.color as to_thread_color',

                    't1.tube_number as from_tube_number',
                    't2.tube_number as to_tube_number'
                ]);

            if (!$item) {
                throw new \Exception("Empalme no encontrado");
            }

            $data = [
                'id' => $item->id,
                'from_thread_id' => $item->from_thread_id,
                'from_tube_number' => $item->from_tube_number,
                'from_thread_number' => $item->from_thread_number,
                'from_thread_color' => $item->from_thread_color,

                'to_thread_id' => $item->to_thread_id,
                'to_tube_number' => $item->to_tube_number,
                'to_thread_number' => $item->to_thread_number,
                'to_thread_color' => $item->to_thread_color,

                'splice_type' => $item->splice_type,
                'status' => $item->status,

                'datecreated_label' => FG::formatDateTimeHuman($item->created_at)
            ];

            $response['success'] = true;
            $response['data'] = $data;
            $response['message'] = 'Detalle de empalme';
        } catch (\Exception $e) {

            $response['success'] = false;
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

            $splice = FiberSplice::where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$splice) {
                $response['success'] = false;
                $response['message'] = 'Empalme no encontrado.';
                return $response;
            }

            $from_thread_id = trim($input['from_thread_id']);
            $to_thread_id = trim($input['to_thread_id']);
            $splice_type = trim($input['splice_type']);
            $status = trim($input['status']);

            if (empty($from_thread_id) || empty($to_thread_id) || empty($splice_type) || empty($status)) {
                $response['success'] = false;
                $response['message'] = 'Todos los campos son requeridos.';
                return $response;
            }

            if ($from_thread_id == $to_thread_id) {
                $response['success'] = false;
                $response['message'] = 'No puedes empalmar el mismo hilo.';
                return $response;
            }

            $from_thread = FiberThread::where('id', $from_thread_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$from_thread) {
                $response['success'] = false;
                $response['message'] = 'Hilo de origen no encontrado.';
                return $response;
            }

            $to_thread = FiberThread::where('id', $to_thread_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$to_thread) {
                $response['success'] = false;
                $response['message'] = 'Hilo de destino no encontrado.';
                return $response;
            }

            $exists = FiberSplice::where('id', '!=', $id)
                ->where(function ($q) use ($from_thread_id, $to_thread_id) {
                    $q->where(function ($x) use ($from_thread_id, $to_thread_id) {
                        $x->where('from_thread_id', $from_thread_id)
                            ->where('to_thread_id', $to_thread_id);
                    })->orWhere(function ($x) use ($from_thread_id, $to_thread_id) {
                        $x->where('from_thread_id', $to_thread_id)
                            ->where('to_thread_id', $from_thread_id);
                    });
                })
                ->whereNull('deleted_at')
                ->first();

            if ($exists) {
                $response['success'] = false;
                $response['message'] = 'Ese empalme ya existe.';
                return $response;
            }

            $splice->from_thread_id = $from_thread_id;
            $splice->to_thread_id = $to_thread_id;
            $splice->splice_type = $splice_type;
            $splice->status = $status;
            $splice->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $splice;
            $response['message'] = "Empalme actualizado correctamente.";
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
        DB::beginTransaction();
        try {
            $id = $request->getAttribute('id');

            $splice = FiberSplice::where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$splice) {
                $response['success'] = false;
                $response['message'] = "Empalme no encontrado.";
                return $response;
            }

            $splice->deleted_at = FG::getDateHour();
            $splice->save();

            DB::commit();

            $response['success'] = true;
            $response['data'] = $splice;
            $response['message'] = "Empalme fue eliminado correctamente";
        } catch (\Exception $e) {
            DB::rollBack();
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function bythread($request)
    {
        $response = FG::responseDefault();
        try {
            $thread_id = $request->getAttribute('thread_id');

            $thread = FiberThread::where('id', $thread_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$thread) {
                $response['success'] = false;
                $response['message'] = "Hilo no encontrado.";
                return $response;
            }

            $items = FiberSplice::from('fiber_splices as fs')
                ->leftJoin('fiber_threads as ft1', 'ft1.id', '=', 'fs.from_thread_id')
                ->leftJoin('fiber_threads as ft2', 'ft2.id', '=', 'fs.to_thread_id')
                ->leftJoin('tubes as t1', 't1.id', '=', 'ft1.tube_id')
                ->leftJoin('tubes as t2', 't2.id', '=', 'ft2.tube_id')
                ->where(function ($q) use ($thread_id) {
                    $q->where('fs.from_thread_id', $thread_id)
                        ->orWhere('fs.to_thread_id', $thread_id);
                })
                ->whereNull('fs.deleted_at')
                ->orderBy('fs.id', 'desc')
                ->get([
                    'fs.id',
                    'fs.from_thread_id',
                    'fs.to_thread_id',
                    'fs.splice_type',
                    'fs.status',
                    'fs.created_at',

                    'ft1.thread_number as from_thread_number',
                    'ft1.color as from_thread_color',

                    'ft2.thread_number as to_thread_number',
                    'ft2.color as to_thread_color',

                    't1.tube_number as from_tube_number',
                    't2.tube_number as to_tube_number'
                ]);

            $data = $items->map(function ($item) {

                return [
                    'id' => $item->id,

                    'from_thread_id' => $item->from_thread_id,
                    'from_tube_number' => $item->from_tube_number,
                    'from_thread_number' => $item->from_thread_number,
                    'from_thread_color' => $item->from_thread_color,

                    'to_thread_id' => $item->to_thread_id,
                    'to_tube_number' => $item->to_tube_number,
                    'to_thread_number' => $item->to_thread_number,
                    'to_thread_color' => $item->to_thread_color,

                    'splice_type' => $item->splice_type,
                    'status' => $item->status,

                    'datecreated_label' => FG::formatDateTimeHuman($item->created_at)
                ];
            });

            $response['success'] = true;
            $response['data'] = $items;
            $response['message'] = "Empalmes del hilo";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
