<?php

namespace App\Dows;

use App\Middlewares\Application;
use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Models\Area;
use App\Models\User;

class ProfileDow
{

    public function index($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();


            $response['success'] = true;
            $response['data'] = null;
            $response['message'] = 'exito';
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
            $files = $request->getUploadedFiles();

            $user = User::find($id);
            if (!$user) {
                $response['success'] = false;
                $response['message'] = "No se encontró los datos del usuario.";
                return $response;
            }

            if (
                empty($input['name']) || empty($input['paternal_surname']) ||
                empty($input['maternal_surname'])  || empty($input['username'])
            ) {
                $response['success'] = false;
                $response['message'] = "Todos los campos son Obligatorios";
                return $response;
            }

            $photo_id = $user->foto_id;
            if (isset($files['foto'])) {
                $uploadResponse = StorageDow::upload('profile', $files, 'localhost', 'foto');
                if ($uploadResponse['success']) {
                    $photo_id = $uploadResponse['data']->id;
                } else {
                    throw new \Exception("Error al subir la Imagen: " . $uploadResponse['message']);
                }
            }

            $user->name = $input['name'];
            $user->paternal_surname = $input['paternal_surname'];
            $user->maternal_surname = $input['maternal_surname'] ?? $user->maternal_surname;
            $user->username = $input['username'] ?? $user->username;
            $user->foto_id = $photo_id;
            $user->save();

            $response['success'] = true;
            $response['data'] = $user;
            $response['message'] = "Datos Actualizado correctamente.";
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function password($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $id = $request->getAttribute('id');

            $new_password = isset($input['new_password']) ? trim($input['new_password']) : '';
            $repet_password = isset($input['repet_pasword']) ? trim($input['repet_pasword']) : '';

            if (empty($new_password) || empty($repet_password)) {
                $response['success'] = false;
                $response['message'] = 'Ingrese la nueva contraseña y su repetición.';
                return $response;
            }

            if ($new_password !== $repet_password) {
                $response['success'] = false;
                $response['message'] = 'Las contraseñas no coinciden.';
                return $response;
            }

            if (strlen($new_password) < 6) {
                $response['success'] = false;
                $response['message'] = 'La contraseña debe tener al menos 6 caracteres.';
                return $response;
            }

            $user = User::find($id);
            if (!$user) {
                $response['success'] = false;
                $response['message'] = 'No se encontró el usuario.';
                return $response;
            }

            $user->password = password_hash($new_password, PASSWORD_BCRYPT);
            $user->save();

            $response['success'] = true;
            $response['data'] = null;
            $response['message'] = 'La contraseña fue actualizada correctamente.';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function email($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $id = $request->getAttribute('id');

            $new_email = isset($input['email']) ? trim($input['email']) : '';
            $password = isset($input['password']) ? $input['password'] : null;

            if (empty($new_email)) {
                $response['success'] = false;
                $response['message'] = 'Ingrese el correo electrónico.';
                return $response;
            }

            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $response['success'] = false;
                $response['message'] = 'Formato de correo inválido.';
                return $response;
            }

            $exists = DB::table('users')
                ->where('email', $new_email)
                ->whereNull('deleted_at')
                ->where('id', '!=', $id)
                ->first();

            if ($exists) {
                $response['success'] = false;
                $response['message'] = 'El correo ya está en uso por otro usuario.';
                return $response;
            }

            $user = User::find($id);
            if (!$user) {
                $response['success'] = false;
                $response['message'] = 'No se encontró el usuario.';
                return $response;
            }

            if (!is_null($password)) {
                if (!password_verify($password, $user->password)) {
                    $response['success'] = false;
                    $response['message'] = 'Contraseña incorrecta.';
                    return $response;
                }
            }

            $user->email = $new_email;
            $user->save();

            $response['success'] = true;
            $response['data'] = $user;
            $response['message'] = 'Correo actualizado correctamente.';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
