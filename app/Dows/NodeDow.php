<?php

namespace App\Dows;

use App\Middlewares\Application;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Models\Node;

class NodeDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = Node::whereNull('deleted_at')
                ->orderBy('id', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
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

            $items = Node::whereNull('deleted_at')
                ->orderBy('name', 'asc')
                ->get();

            $items = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
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

            $name = trim($input['name']);
            $code = trim($input['code']);
            $latitude = trim($input['latitude']);
            $longitude = trim($input['longitude']);
            $reference = trim($input['reference']);
            $district = trim($input['district']);
            $city = trim($input['city']);

            if (empty($name) || empty($code) || empty($latitude) || empty($longitude) || empty($district) || empty($city)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            $node = new Node();
            $node->name = $name;
            $node->code = $code;
            $node->latitude = $latitude;
            $node->longitude = $longitude;
            $node->reference = $reference;
            $node->district = $district;
            $node->city = $city;
            $node->save();

            $response['success'] = true;
            $response['data'] = $node;
            $response['message'] = 'Mufa creado correctamente';
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

            $node = Node::find($id);
            if (!$node) {
                $response['success'] = false;
                $response['message'] = "Mufa no encontrada.";
                return $response;
            }

            if (empty($input['name']) || empty($input['code']) || empty($input['latitude']) || empty($input['longitude']) || empty($input['district']) || empty($input['city'])) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            $node->name = $input['name'];
            $node->code = $input['code'];
            $node->latitude = $input['latitude'];
            $node->longitude = $input['longitude'];
            $node->reference = $input['reference'];
            $node->district = $input['district'];
            $node->city = $input['city'];
            $node->save();

            $response['success'] = true;
            $response['data'] = $node;
            $response['message'] = "Mufa actualizada correctamente";
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

            $node = Node::find($id);
            if (!$node) {
                $response['success'] = false;
                $response['message'] = "Mufa no encontrada.";
                return $response;
            }

            $node->deleted_at = FG::getDateHour();
            $node->status = 'inactive';
            $node->save();

            $response['success'] = true;
            $response['data'] = $node;
            $response['message'] = "Mufa fue eliminada correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
