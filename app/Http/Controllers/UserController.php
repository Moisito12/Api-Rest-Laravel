<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{
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
                $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);
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

    public function login(Request $request)
    {
        return "Acción de registro en api";
    }
}
