<?php

namespace App\Dows;

use App\Middlewares\Application;
use App\Models\Client;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;

class ClientDow
{

    public function index($request)
    {
        $response = FG::responseDefault();

        try {

            $input = $request->getParsedBody();

            $page = isset($input['page']) ? (int)$input['page'] : 1;
            $perPage = 10;

            $query = Client::whereNull('deleted_at')
                ->orderBy('id', 'desc');

            $total = $query->count();

            $items = $query
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $data = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'dni' => $item->dni,
                    'name' => $item->name,
                    'document_number' => $item->document_number,
                    'phone' => $item->phone,
                    'address' => $item->address,
                    'district' => $item->district,
                    'city' => $item->city,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
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

            $items = Client::whereNull('deleted_at')
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

            $dni = trim($input['dni']);
            $name = trim($input['name']);
            $phone = trim($input['phone']);
            $address = trim($input['address']);
            $district = trim($input['district']);
            $city = trim($input['city']);
            $latitude = trim($input['latitude']);
            $longitude = trim($input['longitude']);


            if (empty($dni) || empty($name)  || empty($phone) || empty($address) || empty($district) || empty($city) || empty($latitude) || empty($longitude)) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios";
                return $response;
            }

            if (!$this->validateDniUnique($dni)) {
                $response['success'] = false;
                $response['message'] = "El DNI ya está registrado para otro cliente.";
                return $response;
            }

            $client = new Client();
            $client->dni = $dni;
            $client->name = $name;
            $client->phone = $phone;
            $client->address = $address;
            $client->district = $district;
            $client->city = $city;
            $client->latitude = $latitude;
            $client->longitude = $longitude;
            $client->save();

            $response['success'] = true;
            $response['data'] = $client;
            $response['message'] = 'Cliente registrado correctamente';
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

            $client = Client::find($id);
            if (!$client) {
                $response['success'] = false;
                $response['message'] = "Cliente no encontrado.";
                return $response;
            }

            if (empty($input['dni']) || empty($input['name']) || empty($input['phone']) || empty($input['address']) || empty($input['district']) || empty($input['city']) || empty($input['latitude']) || empty($input['longitude'])) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son obligatorios.";
                return $response;
            }

            if (!$this->validateDniUnique($input['dni'], $id)) {
                $response['success'] = false;
                $response['message'] = "El DNI ya está registrado para otro cliente.";
                return $response;
            }

            $client->dni = $input['dni'];
            $client->name = $input['name'];
            $client->document_number = $input['document_number'];
            $client->phone = $input['phone'];
            $client->address = $input['address'];
            $client->district = $input['district'];
            $client->city = $input['city'];
            $client->latitude = $input['latitude'];
            $client->longitude = $input['longitude'];
            $client->save();

            $response['success'] = true;
            $response['data'] = $client;
            $response['message'] = "Cliente actualizado correctamente";
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

            $client = Client::find($id);
            if (!$client) {
                $response['success'] = false;
                $response['message'] = "Cliente no encontrado.";
                return $response;
            }

            $client->deleted_at = FG::getDateHour();
            $client->status = 'inactive';
            $client->save();

            $response['success'] = true;
            $response['data'] = $client;
            $response['message'] = "Cliente fue eliminado correctamente";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    private function validateDniUnique($dni, $excludeId = null)
    {
        $query = Client::where('dni', $dni)->whereNull('deleted_at');
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return !$query->exists();
    }
}
