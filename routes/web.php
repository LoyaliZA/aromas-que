<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;

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
    // Si el usuario ya inició sesión, lo redirigimos a su panel según su rol
    if (Auth::check()) {
        $role = Auth::user()->role;
        
        if ($role === 'ADMIN') return redirect()->route('admin.dashboard');
        if ($role === 'MANAGER') return redirect()->route('gerencia.dashboard');
        if ($role === 'CHECKER') return redirect()->route('recepcion.dashboard');
        if ($role === 'SELLER') return redirect()->route('ventas.dashboard');
        
        // NUEVO: Redirección para Auxiliar desde la raíz
        if ($role === 'AUXILIAR') return redirect()->route('auxiliar.dashboard');
        
        return redirect()->route('dashboard');
    }
    
    // Si no está autenticado, lo forzamos a ir al login
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    ->middleware(['auth', 'role:ADMIN']) 
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        
        // --- NUEVAS RUTAS: REPORTES Y AUDITORÍA ---
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/audit', [ReportController::class, 'audit'])->name('reports.audit');
        
        Route::get('/tv-ads', [App\Http\Controllers\Admin\TvAdController::class, 'index'])->name('tv_ads.index');
        Route::post('/tv-ads', [App\Http\Controllers\Admin\TvAdController::class, 'store'])->name('tv_ads.store');
        Route::post('/tv-ads/{tvAd}/toggle', [App\Http\Controllers\Admin\TvAdController::class, 'toggle'])->name('tv_ads.toggle');
        Route::delete('/tv-ads/{tvAd}', [App\Http\Controllers\Admin\TvAdController::class, 'destroy'])->name('tv_ads.destroy');
    });

/*
|--------------------------------------------------------------------------
| MÓDULO GERENCIA (Rol: MANAGER)
|--------------------------------------------------------------------------
*/
Route::prefix('gerencia')
    ->name('gerencia.')
    ->middleware(['auth', 'role:MANAGER']) 
    ->group(function () {
        Route::get('/dashboard', [PickupController::class, 'index'])->name('dashboard');
        Route::get('/daily', [PickupController::class, 'daily'])->name('daily'); 
        Route::post('/store', [PickupController::class, 'store'])->name('store');
        Route::put('/update/{id}', [PickupController::class, 'update'])->name('update');
        Route::get('/history', [PickupController::class, 'history'])->name('history');

        // --- NUEVAS RUTAS: REZAGADOS ---
        Route::get('/rezagados', [PickupController::class, 'rezagados'])->name('rezagados.index');
        Route::post('/rezagados/{id}/entregar', [PickupController::class, 'entregarRezagado'])->name('rezagados.entregar');

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
    ->middleware(['auth' => 'auth', 'role:CHECKER']) 
    ->group(function () {
        Route::get('/dashboard', [DeliveryController::class, 'index'])->name('dashboard');
        Route::put('/confirm/{id}', [DeliveryController::class, 'confirm'])->name('confirm'); 
        Route::post('/queue/add', [DeliveryController::class, 'addToQueue'])->name('queue.add'); 
        
        // --- RUTAS NUEVAS ---
        Route::put('/receive/{id}', [DeliveryController::class, 'markAsReceived'])->name('receive');
        Route::get('/queue/list', [DeliveryController::class, 'getQueueList'])->name('queue.list');
        Route::put('/queue/{id}/abandon', [DeliveryController::class, 'markAsAbandoned'])->name('queue.abandon');
    });

/*
|--------------------------------------------------------------------------
| MÓDULO VENTAS (Rol: SELLER)
|--------------------------------------------------------------------------
*/
Route::prefix('ventas')
    ->name('ventas.')
    ->middleware(['auth', 'role:SELLER']) 
    ->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Ventas\QueueController::class, 'index'])->name('dashboard');
        Route::get('/poll', [App\Http\Controllers\Ventas\QueueController::class, 'poll'])->name('poll');
        Route::post('/toggle-break', [App\Http\Controllers\Ventas\QueueController::class, 'toggleBreak'])->name('toggle-break');
        Route::post('/finish-service', [App\Http\Controllers\Ventas\QueueController::class, 'finishService'])->name('finish-service');
        Route::post('/extend-service', [App\Http\Controllers\Ventas\QueueController::class, 'extendService'])->name('extend-service');
    });

/*
|--------------------------------------------------------------------------
| MÓDULO AUXILIAR (Rol: AUXILIAR)
|--------------------------------------------------------------------------
*/
Route::prefix('auxiliar')
    ->name('auxiliar.')
    ->middleware(['auth', 'role:AUXILIAR']) 
    ->group(function () {
        // Dashboard principal del auxiliar
        Route::get('/dashboard', [App\Http\Controllers\Admin\TvAdController::class, 'index'])->name('dashboard');
        
        // Rutas de acciones para anuncios (asegúrate de que los nombres coincidan con la vista)
        Route::post('/tv-ads', [App\Http\Controllers\Admin\TvAdController::class, 'store'])->name('tv_ads.store');
        Route::put('/tv-ads/{tvAd}', [App\Http\Controllers\Admin\TvAdController::class, 'update'])->name('tv_ads.update');
        Route::post('/tv-ads/{tvAd}/toggle', [App\Http\Controllers\Admin\TvAdController::class, 'toggle'])->name('tv_ads.toggle');
        Route::delete('/tv-ads/{tvAd}', [App\Http\Controllers\Admin\TvAdController::class, 'destroy'])->name('tv_ads.destroy');
        
    });

/*
|--------------------------------------------------------------------------
| VISTA PÚBLICA (TV)
|--------------------------------------------------------------------------
*/
Route::get('/tv', [TvController::class, 'index'])->name('tv.public');

require __DIR__.'/auth.php';