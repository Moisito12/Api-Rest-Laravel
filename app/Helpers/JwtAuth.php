<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JWTAuth
{
    public $key;

    public function __construct()
    {
        $this->key = 'MASTER_KEY';
    }
    public function signup($email, $password, $getToken = null)
    {
        // Buscar si existe el usuario en la bd
        $user = User::where([
            'email'  => $email,
            'password' => $password
        ])->first();
        // Comprobar si las credenciales son correctas
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }
        // Generar el token con los datos del usuario identificado
        if ($signup) {
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            // Devolver los datos decoificados o el token, en función de un parámetro
            if (is_null($getToken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
        } else {
            $data = [
                'status' => 'error',
                'message' => 'Login incorrecto'
            ];
        }
        return $data;
    }
}
