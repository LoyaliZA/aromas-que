<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CatalogController;
use App\Http\Controllers\Gerencia\PickupController;
use App\Http\Controllers\Recepcion\DeliveryController;
use App\Http\Controllers\Ventas\QueueController;
use App\Http\Controllers\Public\TvController;
use App\Http\Controllers\Admin\TvAdController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Gerencia\StaffController;
use App\Http\Controllers\Gerencia\ClientRatingController;
use App\Http\Controllers\Logistica\BellaromaController;
use App\Http\Controllers\Logistica\CallCenterController;
use App\Http\Controllers\Logistica\CedisController;





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
        // --- NUEVO: Redirecciones de Logística ---
        if ($role === 'BELLAROMA') return redirect()->route('bellaroma.dashboard');
        if ($role === 'CALLCENTER') return redirect()->route('callcenter.dashboard');
        if ($role === 'CEDIS') return redirect()->route('cedis.dashboard');

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
        Route::get('/dashboard/realtime', [AdminDashboardController::class, 'realTimeDashboard'])->name('dashboard.realtime');
        Route::resource('users', UserController::class);

        // --- RUTAS DE CLIENTES Y CSV ---
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers/import', [CustomerController::class, 'importCsv'])->name('customers.import');
        Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');

        // --- RUTAS: REPORTES Y AUDITORÍA ---
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/real-time', [ReportController::class, 'realTimeData'])->name('reports.realtime'); // <-- NUEVA RUTA
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/audit', [ReportController::class, 'audit'])->name('reports.audit');

        // --- PUBLICIDAD TV ---
        Route::get('/tv-ads', [TvAdController::class, 'index'])->name('tv_ads.index');
        Route::post('/tv-ads', [TvAdController::class, 'store'])->name('tv_ads.store');
        Route::post('/tv-ads/{tvAd}/toggle', [TvAdController::class, 'toggle'])->name('tv_ads.toggle');
        Route::delete('/tv-ads/{tvAd}', [TvAdController::class, 'destroy'])->name('tv_ads.destroy');
        Route::post('/tv-ads/reorder', [TvAdController::class, 'reorder'])->name('tv_ads.reorder');
        Route::post('/tv-ads/{tvAd}/volume', [TvAdController::class, 'updateVolume'])->name('tv_ads.volume');

        // --- RUTAS: MÓDULO DE RESEÑAS Y CALIDAD ---
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/reviews/search', [ReviewController::class, 'search'])->name('reviews.search'); // <-- NUEVA
        Route::get('/reviews/seller/{id}', [ReviewController::class, 'showSeller'])->name('reviews.seller');
        Route::get('/reviews/customer/{id}', [ReviewController::class, 'showCustomer'])->name('reviews.customer');

        // --- CONFIGURACIONES / CATÁLOGOS ---
        Route::get('/settings/catalogs', [CatalogController::class, 'index'])->name('settings.catalogs');
        Route::post('/settings/catalogs/store', [CatalogController::class, 'store'])->name('settings.catalogs.store');
        Route::put('/settings/catalogs/{id}', [CatalogController::class, 'update'])->name('settings.catalogs.update');
        Route::post('/settings/catalogs/{id}/toggle', [CatalogController::class, 'toggle'])->name('settings.catalogs.toggle');
        Route::delete('/settings/catalogs/{id}', [CatalogController::class, 'destroy'])->name('settings.catalogs.destroy');
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
        Route::delete('/destroy/{id}', [PickupController::class, 'destroy'])->name('destroy');
        Route::get('/history', [PickupController::class, 'history'])->name('history');

        // --- RUTAS: REZAGADOS ---
        Route::get('/rezagados', [PickupController::class, 'rezagados'])->name('rezagados.index');
        Route::post('/rezagados/{id}/entregar', [PickupController::class, 'entregarRezagado'])->name('rezagados.entregar');
        // --- Resguardos --
        Route::post('/pickups/preliminar', [PickupController::class, 'storePreliminar'])->name('pickups.storePreliminar');
        // --- RUTAS DE AUDITORIA ---
        Route::post('/pickups/{id}/approve', [PickupController::class, 'approveAudit'])->name('pickups.approveAudit');
        Route::post('/pickups/{id}/reject', [PickupController::class, 'rejectAudit'])->name('pickups.rejectAudit');
        Route::get('/pickups/search-folio', [PickupController::class, 'searchFolio'])->name('pickups.searchFolio');

        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff/toggle', [StaffController::class, 'toggleShift'])->name('staff.toggle');

        // --- RUTAS: CALIFICACIÓN CLIENTES (TABLET) ---
        Route::get('/calificacion-cliente', [ClientRatingController::class, 'index'])->name('calificacion.index');
        Route::get('/calificacion-cliente/recent', [ClientRatingController::class, 'getRecentSales'])->name('calificacion.recent');
        Route::post('/calificacion-cliente/store', [ClientRatingController::class, 'store'])->name('calificacion.store');
    });

/*
|--------------------------------------------------------------------------
| MÓDULO RECEPCIÓN (Rol: CHECKER)
|--------------------------------------------------------------------------
*/
Route::prefix('recepcion')
    ->name('recepcion.')
    ->middleware(['auth', 'role:CHECKER'])
    ->group(function () {
        Route::get('/dashboard', [DeliveryController::class, 'index'])->name('dashboard');

        // Acciones de Resguardos
        Route::put('/confirm/{id}', [DeliveryController::class, 'confirm'])->name('confirm');
        Route::put('/receive/{id}', [DeliveryController::class, 'markAsReceived'])->name('receive');
        Route::post('/preliminar/{id}/complete', [DeliveryController::class, 'completePreliminar'])->name('preliminar.complete');

        // Acciones de Fila (Kiosco)
        Route::post('/queue/add', [DeliveryController::class, 'addToQueue'])->name('queue.add');
        Route::get('/queue/list', [DeliveryController::class, 'getQueueList'])->name('queue.list');
        Route::put('/queue/{id}/abandon', [DeliveryController::class, 'markAsAbandoned'])->name('queue.abandon');

        // --- BUSCADOR DE CLIENTES EN VIVO (Hacía falta) ---
        Route::get('/customers/search', [DeliveryController::class, 'searchCustomers'])->name('customers.search');
    });

/*
|--------------------------------------------------------------------------
| MÓDULO VENTAS (Roles: SELLER, MANAGER, ADMIN)
|--------------------------------------------------------------------------
*/
Route::prefix('ventas')
    ->name('ventas.')
    ->middleware(['auth', 'role:SELLER,MANAGER,ADMIN']) // <-- Agregamos los roles aquí
    ->group(function () {
        Route::get('/dashboard', [QueueController::class, 'index'])->name('dashboard');
        Route::get('/poll', [QueueController::class, 'poll'])->name('poll');
        Route::post('/toggle-break', [QueueController::class, 'toggleBreak'])->name('toggle-break');
        Route::post('/finish-service', [QueueController::class, 'finishService'])->name('finish-service');
        Route::post('/submit-rating', [QueueController::class, 'submitRating'])->name('submit-rating');

        // --- RUTAS NUEVAS PARA GERENCIA (RETENCIÓN) ---
        Route::get('/retention/list', [QueueController::class, 'getRetentionList'])->name('retention.list');
        Route::post('/retention/reassign', [QueueController::class, 'reassignRetention'])->name('retention.reassign');
        
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
        Route::get('/dashboard', [TvAdController::class, 'index'])->name('dashboard');

        // Rutas de acciones para anuncios
        Route::post('/tv-ads', [TvAdController::class, 'store'])->name('tv_ads.store');
        Route::put('/tv-ads/{tvAd}', [TvAdController::class, 'update'])->name('tv_ads.update');
        Route::post('/tv-ads/{tvAd}/toggle', [TvAdController::class, 'toggle'])->name('tv_ads.toggle');
        Route::delete('/tv-ads/{tvAd}', [TvAdController::class, 'destroy'])->name('tv_ads.destroy');
        Route::post('/tv-ads/reorder', [TvAdController::class, 'reorder'])->name('tv_ads.reorder');
        Route::post('/tv-ads/{tvAd}/volume', [TvAdController::class, 'updateVolume'])->name('tv_ads.volume');
    });

/*
|--------------------------------------------------------------------------
| NUEVOS MÓDULOS DE LOGÍSTICA
|--------------------------------------------------------------------------
*/
Route::prefix('bellaroma')
    ->name('bellaroma.')
    ->middleware(['auth', 'role:BELLAROMA,ADMIN'])
    ->group(function () {
        Route::get('/dashboard', [BellaromaController::class, 'index'])->name('dashboard');
        Route::get('/history', [BellaromaController::class, 'history'])->name('history');
    });

Route::prefix('callcenter')
    ->name('callcenter.')
    ->middleware(['auth', 'role:CALLCENTER,ADMIN'])
    ->group(function () {
        Route::get('/dashboard', [CallCenterController::class, 'index'])->name('dashboard');
    });

Route::prefix('cedis')
    ->name('cedis.')
    ->middleware(['auth', 'role:CEDIS,ADMIN'])
    ->group(function () {
        Route::get('/dashboard', [CedisController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| VISTA PÚBLICA (TV)
|--------------------------------------------------------------------------
*/
Route::get('/tv', [TvController::class, 'index'])->name('tv.public');

require __DIR__ . '/auth.php';