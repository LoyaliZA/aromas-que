<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Opcional: Si ya estás logueado, que te mande directo al admin
    if (Auth::check() && Auth::user()->role === 'ADMIN') {
        return redirect()->route('admin.dashboard');
    }
    return view('welcome');
});

// Ruta "Dashboard" por defecto de Breeze (necesaria para redirigir fallos o perfiles básicos)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas de Perfil (Estándar de Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| PANEL DE ADMINISTRACIÓN (Rol: ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth']) // IMPORTANTE: Aquí luego pondremos 'role:ADMIN'
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // CRUD de Usuarios/Empleados
        Route::resource('users', UserController::class);
        
    });

require __DIR__.'/auth.php';