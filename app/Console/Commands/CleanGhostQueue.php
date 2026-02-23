<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SalesQueue;
use App\Models\DailyShift;
use Carbon\Carbon;

class CleanGhostQueue extends Command
{
    /**
     * El nombre para ejecutar el comando en terminal.
     */
    protected $signature = 'queue:clean-ghosts';

    /**
     * Descripción del comando.
     */
    protected $description = 'Limpia turnos atascados (fantasmas) y desconecta vendedores al final del día.';

    /**
     * Lógica de ejecución.
     */
    public function handle()
    {
        $now = Carbon::now();

        // 1. Limpiar turnos atascados en "Atención" (Fantasmas)
        $servingClients = SalesQueue::where('status', 'SERVING')->get();
        foreach ($servingClients as $client) {
            $client->update([
                'status' => 'COMPLETED',
                'completed_at' => $now
            ]);
        }

        // 2. Limpiar turnos que se quedaron esperando y nunca pasaron
        $waitingClients = SalesQueue::where('status', 'WAITING')->get();
        foreach ($waitingClients as $client) {
            $client->update([
                'status' => 'ABANDONED',
                'completed_at' => $now
            ]);
        }

        // 3. Forzar desconexión de vendedores que no cerraron sesión
        $activeShifts = DailyShift::whereIn('current_status', ['ONLINE', 'BREAK'])
                                  ->whereDate('work_date', today())
                                  ->get();

        foreach ($activeShifts as $shift) {
            $shift->update([
                'current_status' => 'OFFLINE',
                'last_status_change_at' => $now
            ]);
        }

        $this->info("Limpieza nocturna completada:");
        $this->info("- Turnos cerrados: " . $servingClients->count());
        $this->info("- Turnos abandonados: " . $waitingClients->count());
        $this->info("- Vendedores desconectados: " . $activeShifts->count());
    }
}