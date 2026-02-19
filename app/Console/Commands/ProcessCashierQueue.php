<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesQueue;
use Carbon\Carbon;

class ProcessCashierQueue extends Command
{
    /**
     * El nombre para ejecutar el comando en terminal.
     */
    protected $signature = 'queue:process-cashier';

    /**
     * Descripción del comando.
     */
    protected $description = 'Procesa automáticamente la fila de caja (CASHIER), finalizando clientes tras 2 minutos y pasando al siguiente.';

    /**
     * Lógica de ejecución.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Buscar si hay alguien siendo atendido actualmente en CAJA
        $servingCashier = SalesQueue::where('service_type', 'CASHIER')
                                    ->where('status', 'SERVING')
                                    ->first();

        // 2. Si hay alguien en caja, verificamos si ya pasaron 2 minutos
        if ($servingCashier) {
            $startedAt = Carbon::parse($servingCashier->started_serving_at);
            
            if ($startedAt->diffInMinutes($now) >= 2) {
                // Ya pasaron 2 minutos, lo finalizamos
                $servingCashier->update([
                    'status' => 'COMPLETED',
                    'completed_at' => $now,
                ]);
                
                $this->info("Cliente {$servingCashier->client_name} finalizado en caja.");
                
                // Lo volvemos null para que el bloque 3 sepa que la caja ya está libre
                $servingCashier = null; 
            } else {
                $this->info("El cliente actual en caja aún no cumple los 2 minutos.");
            }
        }

        // 3. Si la caja está libre (porque no había nadie o acabamos de finalizar a uno)
        if (!$servingCashier) {
            // Buscamos al cliente más antiguo que esté esperando ir a caja
            $nextInLine = SalesQueue::where('service_type', 'CASHIER')
                                    ->where('status', 'WAITING')
                                    ->orderBy('queued_at', 'asc')
                                    ->first();

            // Si hay alguien esperando, lo pasamos a la caja automáticamente
            if ($nextInLine) {
                $nextInLine->update([
                    'status' => 'SERVING',
                    'started_serving_at' => $now,
                ]);
                
                $this->info("Cliente {$nextInLine->client_name} ha pasado a caja automáticamente.");
            } else {
                $this->info("Caja libre. No hay clientes en espera.");
            }
        }
    }
}