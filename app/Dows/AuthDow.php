<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilities\FG;
use App\Utilities\FirebaseJWT;
use App\Middlewares\Application;
use App\Middlewares\Authenticate;
use App\Models\LoginLog;
use App\Models\LoginParticipantLog;
use App\Utilities\Mailer;
use App\Utilities\Twig;
use App\Models\User;
use App\Models\Role;
use App\Models\StorageFile;
use App\Utilities\Storage;

class AuthDow
{

    public function signin($request)
    {
        $response = FG::responseDefault();
        try {

            $input = $request->getParsedBody();

            $username = $input['username'];
            $password = $input['password'];

            $user = User::with('roles')->where('username', $username)->first();

            if (!$user) {
                throw new \Exception('El usuario no existe');
            }

            if (!password_verify($password, $user->password)) {
                throw new \Exception('Las credenciales son incorrectas');
            }

            $roles = json_decode(json_encode($user->roles));
            $rol = count($roles) ? $roles[0] : null;

            if (!$rol) {
                throw new \Exception('El usuario no tiene ningun rol asociado!');
            }

            $role_id = $rol->pivot->role_id;
            $role = Role::with("permissions")->where("id", $role_id)->first();

            $token = FirebaseJWT::encode(Authenticate::payloadToken([
                'user_id' => $user->id
            ]), Authenticate::keySecretToken());

            $response['success'] = true;
            $response['data'] = compact('user', 'role', 'token');
            $response['message'] = 'Se inicio sesión correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function signout($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $ipAddress = $request->getServerParams()['REMOTE_ADDR'] ?? 'IP no disponible';
            $userAgent = $request->getServerParams()['HTTP_USER_AGENT'] ?? 'No Disponible';
            $platformInfo = $input['platformInfo'];

            $user_id = (int) $input['user_id'];

            $datos = [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'platform' => $platformInfo,
            ];

            $response['success'] = true;
            $response['message'] = 'Se cerró la sesión correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function forgotPassword($request)
    {
        $response = FG::responseDefault();
        try {

            $input = $request->getParsedBody();
            $email = $input['email'];
            $redirect =  Application::getItem('redirect');

            if (!$email) {
                throw new \Exception('The email is required.');
            }

            $user = DB::table('users')->where('deleted_at')->where('email', $email)->first();
            if (!$user) {
                throw new \Exception('El usuario no está registrado en la plataforma.');
            }


            $time = time();
            $payload = array(
                'iat' => $time,
                'exp' => $time + 216000, // 5 días
                'user_id' => $user->id
            );
            $token = FirebaseJWT::encode($payload);

            $url = $redirect . "?key=" . urlencode($token);
            //return $url;
            $fullname = $user->name . " " . $user->lastname;
            $mailer = new Mailer();
            $body = Twig::render('mail/recover.password.twig', compact('email', 'url', 'fullname'));
            $params = array('subject' => 'Recuperar Contraseña.', 'body' => "$body", 'recipients' => array());
            $recipients = array();
            array_push($recipients, array('email' => $user->email, 'name' => $user->name));
            $params['recipients'] = $recipients;
            $result = $mailer->sendEmail($params);
            return $result;
            if (!$result['success']) {
                throw new \Exception('No se pudo enviar el correo electrónico.');
            }

            $response['success'] = true;
            $response['message'] = 'Se envío correctamente.';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function verifyKeyPassword($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $token = trim($input['token']);

            if (!$token) {
                throw new \Exception('El campo token es requerido.');
            }

            $decode = FirebaseJWT::decode($token);

            $user_id = $decode->user_id;

            $user = DB::table('users')->where('deleted_at')->where('id', $user_id)->first();
            if (!$user) {
                throw new \Exception('El usuario no existe en la plataforma.');
            }

            $response['success'] = true;
            $response['data']    = compact('user_id');
            $response['message'] = 'successfully';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function restorePassword($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $new_password = $input['new_password'];
            $repeat_password = $input['repeat_password'];
            $user_id = $input['user_id'];

            if (!$new_password) {
                throw new \Exception('El campo password es requerido.');
            }
            if (!$repeat_password) {
                throw new \Exception('El campo repeat_password es requerido.');
            }

            if ($new_password != $repeat_password) {
                throw new \Exception('Las contraseñas no coinciden.');
            }
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            DB::table('users')->where('id', $user_id)->update([
                'password' => $hashedPassword
            ]);

            $response['success'] = true;
            $response['message'] = 'Se restableció correctamente su contraseña, ahora inicie sesión por favor';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }

    public function verifyToken($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $user_id = Application::getItem("user_id");
            $baseDomain  = $_ENV['API_ADMIN_URL'];
            $user = User::with('roles')->where('id', $user_id)->first();
            if (!$user) {
                throw new \Exception('No se encontro al usuario.', 1);
            }

            $user->full_name = $user->name . ' ' . strtoupper(substr($user->paternal_surname, 0, 1)) . '. ' . $user->maternal_surname;
            if ($user->foto_id) {
                $foto = StorageFile::where('id', $user->foto_id)->first();
                $user->foto_path =  $foto ? $baseDomain . $foto->path : null;
            } else {
                $user->foto_path = null;
            }

            $roles = json_decode(json_encode($user->roles));
            $rol = count($roles) ? $roles[0] : null;

            if (!$rol) {
                throw new \Exception('El usuario no tiene ningun rol asociado');
            }

            $role_id = $rol->pivot->role_id;
            $role = Role::with("permissions")->where("id", $role_id)->first();

            $token = FirebaseJWT::encode(Authenticate::payloadToken([
                'user_id' => $user->id
            ]), Authenticate::keySecretToken());

            $response['success'] = true;
            $response['data'] = compact('user', 'role', 'token');
            $response['message'] = 'verify token';
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $response['errors'][] = $error;
            $response['message'] = $error;
        }
        return $response;
    }

    public function signup($request)
    {
        $response = FG::responseDefault();
        try {
            $input = $request->getParsedBody();

            $name = trim($input['name']);
            $paternal_surname = trim($input['paternal_surname']);
            $maternal_surname = isset($input['maternal_surname']) ? trim($input['maternal_surname']) : null;
            $username = trim($input['username']);
            $email = trim($input['email']);
            $role_id = trim($input['role_id']);
            $password = trim($input['password']);

            $user = DB::table('users AS US')->where('US.email', $email)->first();
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $name,
                    'paternal_surname' => $paternal_surname,
                    'maternal_surname' => $maternal_surname,
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'status' => 1
                ]);
            } else {
                if ($user->deleted_at) {
                    $userId = $user->id;
                    DB::table('users')->where('id', $userId)->update(['deleted_at' => NULL]);
                } else {
                    throw new \Exception('Ya existe un usuario con este correo');
                }
            }

            DB::table('user_company_role')->where('user_id', $userId)->delete();

            DB::table('user_company_role')->insertGetId([
                'user_id' => $userId,
                'role_id' => $role_id,
            ]);

            $response['success'] = true;
            $response['message'] = 'Se registró correctamente';
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $response;
    }
}
