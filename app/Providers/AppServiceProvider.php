<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Agregar esta línea
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Si estamos en entorno de producción, forzamos HTTPS
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Livewire::forceAssetInjection();

        // Register Observers
        \App\Models\Role::observe(\App\Observers\RoleObserver::class);
        \App\Models\Department::observe(\App\Observers\DepartmentObserver::class);
        \App\Models\JobPosition::observe(\App\Observers\JobPositionObserver::class);
        \App\Models\ClientType::observe(\App\Observers\ClientTypeObserver::class);
        \App\Models\ServiceType::observe(\App\Observers\ServiceTypeObserver::class);
        \App\Models\BreakReason::observe(\App\Observers\BreakReasonObserver::class);
        \App\Models\QueueStatus::observe(\App\Observers\QueueStatusObserver::class);
        \App\Models\QueueSource::observe(\App\Observers\QueueSourceObserver::class);
    }
}