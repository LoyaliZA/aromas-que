<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth']) // Protegemos el acceso (Login requerido)
    ->group(function () {

        // Dashboard Principal: /admin/dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Gestión de Usuarios y Empleados (Resource Controller)
        // Esto genera automáticamente: index, create, store, edit, update, destroy
        Route::resource('users', UserController::class);

        // Aquí agregaremos luego las rutas de Publicidad (TV) y Reportes
    });

    require __DIR__.'/auth.php'; // Rutas de autenticación (Login, Register, etc.) Breeze Laravel