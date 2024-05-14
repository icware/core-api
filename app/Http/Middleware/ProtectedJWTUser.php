<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class ProtectedJWTUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if(!$user->active){
                return response()->json(['erro'=>'Usuário não autorizado'], Response::HTTP_UNAUTHORIZED);
            }

            // Verifique se o usuário é super, se sim, ignore a verificação da company
            if (!$user->super) {
                if (!$user->company) {
                    return response()->json(['error' => 'Usuário não pertence a nenhuma company'], 401);
                }
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Acesso expirado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Usuário não autorizado'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        return $next($request);
    }
}
