<?php

namespace App\Http\Controllers;

use App\Helpers\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{
    // Iniciando el método de registro
    public function register(Request $request)
    {
        // Recojer los datos el usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        // Validar datos
        $validate = Validator::make($params_array, [
            'name'  => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);
        if (!empty($params) && !empty($params_array)) {
            // Limpiar los datos
            $params_array = array_map('trim', $params_array);
            if ($validate->fails()) {
                $data = [
                    'status'  => 'error',
                    'code'  => 404,
                    'message' => $validate->errors()
                ];
            } else {
                // La validación de datos se paso correctamente

                // Cifrar la contraseña
                $pwd = hash('sha256', $params->password);
                // Crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                // Guardar el usuario
                $user->save();
                $data = [
                    'status'  => 'success',
                    'code'  => 200,
                    'message' => 'El usuario se creó correctamente',
                    'user' => $user
                ];
            }
        } else {
            $data = [
                'status'  => 'error',
                'code'  => 500,
                'message' => 'No se mandaron los datos correctamente'
            ];
        }
        return response()->json($data, $data['code']);
    }
    // finalizando el método de registro


    // iniciando el método de loguin 
    public function login(Request $request)
    {

        $jwtAuth = new JWTAuth();
        // Recibir datos por POST
        $json = $request->input('json', true);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        // Validar esos datos
        $validate = Validator::make($params_array, [
            'email'  => 'required|email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            // La validación ha fallado

            $signup = array(
                'status' => 'error',
                'code'  => 404,
                'message' => 'El usuario no se ha podido identificar',
                'error' => $validate->fails()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);

            // Devolver los datos o el token
            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->gettoken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }
        return response()->json($signup, 200);
    }
    // finalizando el método de loguin 


    // inciando el método de actulización de datos
    public function update(Request $request)
    {
        $token = $request->header('Authorization');
        $jwtAuth = new JWTAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            echo "<h1>Login Correcto</h1>";
        } else {
            echo "<h1>Login Incorrecto</h1>";
        }
        die();
    }
    // finalizando el método de actulización de datos
}
