<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// NUESTRO PILOTO AUTOMATICO DE CAJA
Schedule::command('queue:process-cashier')->everyMinute();

// EL BARRENDERO: Limpieza automática de la cola fantasma todos los días a las 11:59 PM
Schedule::command('queue:clean-ghosts')->dailyAt('23:59');