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
        // Si es ADMIN, lo mandamos a su panel exclusivo
        if ($request->user()->role === 'ADMIN') {
            return redirect()->intended(route('admin.dashboard'));
        }
        if ($request->user()->role === 'MANAGER') {
            return redirect()->intended(route('gerencia.dashboard'));
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