<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {

        // 1. Verificamos que el usuario este logueado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        //2. Verrificamos si es admin lo dejamos pasar a cualquier lado

        if ($user->role === 'ADMIN') {
            return $next($request);
        }

        // 3. Si no es admin, revisamos si su rol esta en la lista de los permitidos para esa ruta

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 4. Si no tiene permiso, lo mandamos a una pantalla de error 403 (Acceso Denegado)
        abort(403, 'Acceso Denegado. No tienes permisos para ver esta sección.');
        
    }
}
