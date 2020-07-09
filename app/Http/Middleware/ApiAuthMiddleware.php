<?php

namespace App\Http\Middleware;

use App\Helpers\JWTAuth;
use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new JWTAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            return $next($request);
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'User not logued'
            ];
            return response()->json($data, $data['code']);
        }
    }
}
