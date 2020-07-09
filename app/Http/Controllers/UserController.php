<?php

namespace App\Http\Controllers;

use App\Helpers\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

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
        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JWTAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            // Recojer los datos por post
            $json = $request->input('json', null);
            $params_array = json_decode($json, true);

            // Sacar el usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Validar datos
            $validate = Validator::make($params_array, [
                'name' => 'required',
                'surname' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // Actualizar usuarios en la bdd
            $user_update = User::where('id', $user->sub)->update($params_array);


            // Devolver array con resultado
            $data = [
                'code' => 200,
                'status' => 'sucess',
                'user' =>  $params_array
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no está identificado'
            ];
        }
        return response()->json($data, $data['code']);
    }
    // finalizando el método de actulización de datos

    // Inciando el método para subir avatar
    public function upload(Request $request)
    {
        // Recoger datos de la petición
        $image = $request->file('file0');

        // Validación de imagenes
        $validate = Validator::make($request->all(), [
            'file0' => 'required|image'
        ]);
        // Guardar la imagen
        if (!$image || $validate->fails()) {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();
            Storage::disk('users')->put($image_name, file_get_contents($image));

            // Devolver un resultado
            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }
        return response()->json($data, $data['code']);
    }
    // finalizando el método para subir avatar


    // iniciando el método para sacar un avatar
    public function getImage($fileName)
    {
        $isset = Storage::disk('users')->exists($fileName);

        if ($isset) {
            $file = Storage::disk('users')->get($fileName);
            return new Response($file, 200);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al obtener la imagen'
            ];

            return response()->json($data, $data['code']);
        }
    }
    // finalizando el método para sacar un avatar

    // inciando el método para sacar los detalles de un usuario
    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'user' => $user
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No se encontró a ese usuario'
            ];
        }
        return response()->json($data, $data['code']);
    }
    // finalizando el método para sacar los detalles de un usuario
}
