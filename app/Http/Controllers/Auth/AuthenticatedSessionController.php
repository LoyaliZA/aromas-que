<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Autenticación y regeneración de sesión (Seguridad base)
        $request->authenticate();
        $request->session()->regenerate();

        // 2. Lógica de Redirección por ROL
        $role = $request->user()->role;

        if ($role === 'ADMIN') {
            return redirect()->intended(route('admin.dashboard'));
        }
        if ($role === 'MANAGER') {
            return redirect()->intended(route('gerencia.dashboard'));
        }
        if ($role === 'CHECKER') {
            return redirect()->intended(route('recepcion.dashboard'));
        }
        if ($role === 'SELLER') {
            return redirect()->intended(route('ventas.dashboard'));
        }
        // NUEVO: Redirección para el rol Auxiliar
        if ($role === 'AUXILIAR') {
            return redirect()->intended(route('auxiliar.dashboard'));
        }

        // 3. Fallback (Por defecto)
        // Si es Empleado normal, Cliente o no tiene rol, va al dashboard estándar
        return redirect()->intended(route('dashboard', absolute: false));
    }
    
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}