<?php

namespace App\Console\Commands;

use App\Models\DailyShift;
use App\Models\SalesQueue;
use App\Models\ShiftStatusLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanGhostQueue extends Command
{
    protected $signature = 'queue:clean-ghosts';

    protected $description = 'Limpia turnos atascados (fantasmas) y desconecta vendedores al final del día.';

    public function handle()
    {
        $now = Carbon::now();

        $servingClients = SalesQueue::serving()->get();
        foreach ($servingClients as $client) {
            $client->update(array_merge(
                SalesQueue::attributesForStatus('COMPLETED'),
                ['completed_at' => $now]
            ));
        }

        $waitingClients = SalesQueue::withStatusCode('WAITING')->get();
        foreach ($waitingClients as $client) {
            $client->update(array_merge(
                SalesQueue::attributesForStatus('ABANDONED'),
                ['completed_at' => $now]
            ));
        }

        $activeShifts = DailyShift::whereIn('current_status', ['ONLINE', 'BREAK'])
            ->whereDate('work_date', today())
            ->get();

        foreach ($activeShifts as $shift) {
            $previousStatus = $shift->current_status;
            $shift->update(array_merge(
                DailyShift::breakReasonAttributes(null),
                [
                    'current_status' => 'OFFLINE',
                    'last_status_change_at' => $now,
                ]
            ));

            ShiftStatusLog::create([
                'daily_shift_id' => $shift->id,
                'previous_status' => $previousStatus,
                'new_status' => 'OFFLINE',
                'changed_at' => $now,
            ]);
        }

        $this->info('Limpieza nocturna completada:');
        $this->info('- Turnos cerrados: ' . $servingClients->count());
        $this->info('- Turnos abandonados: ' . $waitingClients->count());
        $this->info('- Vendedores desconectados: ' . $activeShifts->count());
    }
}
