<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;
use App\User;

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
}
