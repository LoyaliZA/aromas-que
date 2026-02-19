<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;

// --- NUEVOS CONTROLADORES (MODULOS) ---
use App\Http\Controllers\Gerencia\PickupController;
use App\Http\Controllers\Recepcion\DeliveryController;
use App\Http\Controllers\Ventas\QueueController;
use App\Http\Controllers\Public\TvController;

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

/*
|--------------------------------------------------------------------------
| MÓDULO GERENCIA (Rol: MANAGER)
|--------------------------------------------------------------------------
*/
Route::prefix('gerencia')
    ->name('gerencia.')
    ->middleware(['auth']) 
    ->group(function () {
        Route::get('/dashboard', [PickupController::class, 'index'])->name('dashboard');
        
        // --- NUEVA RUTA OPERATIVA ---
        Route::get('/daily', [PickupController::class, 'daily'])->name('daily'); // Tabla de trabajo
        
        Route::post('/store', [PickupController::class, 'store'])->name('store');
        
        // --- RUTA PARA EDITAR ---
        Route::put('/update/{id}', [PickupController::class, 'update'])->name('update');
        
        Route::get('/history', [PickupController::class, 'history'])->name('history');

        Route::get('/staff', [App\Http\Controllers\Gerencia\StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff/toggle', [App\Http\Controllers\Gerencia\StaffController::class, 'toggleShift'])->name('staff.toggle');
    });

/*
|--------------------------------------------------------------------------
| MÓDULO RECEPCIÓN (Rol: CHECKER)
|--------------------------------------------------------------------------
*/
Route::prefix('recepcion')
    ->name('recepcion.')
    ->middleware(['auth']) 
    ->group(function () {
        Route::get('/dashboard', [DeliveryController::class, 'index'])->name('dashboard');
        
        // --- NUEVAS RUTAS ---
        Route::put('/confirm/{id}', [DeliveryController::class, 'confirm'])->name('confirm'); // Procesar entrega
        Route::post('/queue/add', [DeliveryController::class, 'addToQueue'])->name('queue.add'); // Ingresar a cola
    });

/*
|--------------------------------------------------------------------------
| MÓDULO VENTAS (Rol: SELLER)
|--------------------------------------------------------------------------
*/
Route::prefix('ventas')
    ->name('ventas.')
    ->middleware(['auth']) 
    ->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Ventas\QueueController::class, 'index'])->name('dashboard');
        Route::get('/poll', [App\Http\Controllers\Ventas\QueueController::class, 'poll'])->name('poll');
        Route::post('/toggle-break', [App\Http\Controllers\Ventas\QueueController::class, 'toggleBreak'])->name('toggle-break');
        Route::post('/finish-service', [App\Http\Controllers\Ventas\QueueController::class, 'finishService'])->name('finish-service');
        // Nota: Quitamos toggle-shift de aquí porque ahora es exclusivo de gerencia/staff
    });

/*
|--------------------------------------------------------------------------
| VISTA PÚBLICA (TV)
|--------------------------------------------------------------------------
*/
Route::get('/tv', [TvController::class, 'index'])->name('tv.public');


require __DIR__.'/auth.php';