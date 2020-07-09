<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;
use App\Helpers\JWTAuth;
use App\User;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    # Get All posts
    public function index()
    {
        $posts = Post::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ]);
    }

    # Get a post detail
    public function show($id)
    {
        $post = Post::find($id);

        if (is_object($post)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post'  => $post
            );
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message'  => 'No se encontró el post solicitado'
            );
        }
        return response()->json($data, $data['code']);
    }

    #Store a new post
    public function store(Request $request)
    {
        // Recojer los parámetros por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // conseguir usuario indentificado
            $jwtAuth = new JWTAuth();
            $token = $request->header('Authorization', null);
            $user = $jwtAuth->checkToken($token, true);
            // Validar los datos
            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message'  => 'Error con la validación de los datos'
                );
            } else {
                // Creamos el objeto de post
                $post = new Post();
                $post->title = $params->title;
                $post->user_id = $user->sub;
                $post->content = $params->content;
                $post->category_id = $params->category_id;
                $post->image = $params->image;
                $post->save();

                // Devolver una respuesta
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user'  => $post
                );
            }
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message'  => 'No se recibieron los datos para el post'
            );
        }
        return response()->json($data, $data['code']);
    }

    # update the posts
    public function update($id, Request $request)
    {
        // Recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Validar los datos
            $validate = Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Error en la validación de los datos'
                );
            } else {
                // Eliminar lo que no queremos actualizar
                unset($params_array['id']);
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                unset($params_array['user']);

                // Actualizar el registro en concreto
                $post = Post::where('id', $id)->update($params_array);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'chages' => $params_array
                );
            }
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'No se mandaron los datos correctamente'
            );
        }
        return response()->json($data, $data['code']);
    }
}
