<?php

namespace App\Console\Commands;

use App\Models\SalesQueue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessCashierQueue extends Command
{
    protected $signature = 'queue:process-cashier';

    protected $description = 'Procesa automáticamente la fila de caja (CASHIER), finalizando clientes tras 2 minutos y pasando al siguiente.';

    public function handle()
    {
        $now = Carbon::now();

        $servingCashier = SalesQueue::cashier()->serving()->first();

        if ($servingCashier) {
            $startedAt = Carbon::parse($servingCashier->started_serving_at);

            if ($startedAt->diffInMinutes($now) >= 2) {
                $servingCashier->update(array_merge(
                    SalesQueue::attributesForStatus('COMPLETED'),
                    ['completed_at' => $now]
                ));

                $this->info("Cliente {$servingCashier->client_name} finalizado en caja.");
                $servingCashier = null;
            } else {
                $this->info('El cliente actual en caja aún no cumple los 2 minutos.');
            }
        }

        if (!$servingCashier) {
            $nextInLine = SalesQueue::cashier()
                ->waiting()
                ->first();

            if ($nextInLine) {
                $nextInLine->update(array_merge(
                    SalesQueue::attributesForStatus('SERVING'),
                    ['started_serving_at' => $now]
                ));

                $this->info("Cliente {$nextInLine->client_name} ha pasado a caja automáticamente.");
            } else {
                $this->info('Caja libre. No hay clientes en espera.');
            }
        }
    }
}
