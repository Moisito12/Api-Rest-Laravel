<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }
    # List all categories
    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    # Detail of one category
    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = array(
                'code' => 200,
                'status' => 'status',
                'category' => $category
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error al sacar la categoría'
            );
        }
        return response()->json($data, $data['code']);
    }

    #Post a new category
    public function store(Request $request)
    {
        // recojer los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        // Validar los datos
        $validate = Validator::make($params_array, [
            'name' => 'required',
        ]);
        if ($validate->fails()) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'Error con la validacion de categorias'
            );
        } else {
            // Crear el objeto
            $category = new Category();

            $category->name = $params_array['name'];
            $category->save();

            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        }
        // Devolver una respuesta
        return response()->json($data, $data['code']);
    }
}
