<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailyShift;
use App\Models\SalesQueue;
use App\Models\ShiftStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QueueController extends Controller
{
    public function index()
    {
        $sellers = $this->getSellersList();
        $clientsWaiting = SalesQueue::waiting()->sales()->count();
        return view('ventas.dashboard', compact('sellers', 'clientsWaiting'));
    }

    public function poll()
    {
        $this->runMatchmaker();

        $sellers = $this->getSellersList();
        $clientsWaiting = SalesQueue::waiting()->sales()->count();

        // Detectar asignación excluyendo las Retenciones usando el sufijo -R
        $recentAssignment = SalesQueue::where('status', 'SERVING')
            ->where('turn_number', 'not like', '%-R')
            ->where('started_serving_at', '>=', now()->subSeconds(2))
            ->with('assignedShift.employee')
            ->first();

        $alertData = null;
        if ($recentAssignment && $recentAssignment->assignedShift) {
            $alertData = [
                'client' => $recentAssignment->client_name,
                'client_type' => $recentAssignment->client_type,
                'has_disability' => $recentAssignment->has_disability,
                'seller' => $recentAssignment->assignedShift->employee->full_name,
                'folio'  => $recentAssignment->turn_number
            ];
        }

        $html = view('ventas.partials.sellers-grid', compact('sellers'))->render();

        return response()->json([
            'html' => $html,
            'waiting' => $clientsWaiting,
            'alert' => $alertData
        ]);
    }

    public function toggleBreak(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:daily_shifts,id',
            'reason' => 'nullable|string'
        ]);

        $shift = DailyShift::findOrFail($request->shift_id);
        $previousStatus = $shift->current_status;

        // ACCIÓN: ENTRAR A UNA PAUSA
        if ($shift->current_status === 'ONLINE') {
            $reason = $request->reason ?? 'GENERAL';

            if ($reason === 'LUNCH') {
                if ($shift->lunch_seconds_left <= 0) {
                    return back()->with('error', 'El tiempo de comida se ha agotado.');
                }
                $shift->has_taken_lunch = true;
                // LOS 5 MINUTOS DE GRACIA INICIALES
                $statusChangeAt = now()->addMinutes(5); 
            } else {
                $statusChangeAt = now();
            }

            $shift->update([
                'current_status' => 'BREAK',
                'break_reason' => $reason,
                'has_taken_lunch' => $shift->has_taken_lunch,
                'last_status_change_at' => $statusChangeAt
            ]);

            ShiftStatusLog::create([
                'daily_shift_id' => $shift->id,
                'previous_status' => $previousStatus,
                'new_status' => 'BREAK',
                'reason' => $reason,
                'changed_at' => now(), 
            ]);
            
        // ACCIÓN: SALIR DE UNA PAUSA (REGRESAR A PISO)
        } elseif ($shift->current_status === 'BREAK') {
            
            // LÓGICA DE PAUSA DE COMIDA: Restar el tiempo que estuvo ausente
            if ($shift->break_reason === 'LUNCH') {
                // Solo restamos tiempo si ya pasaron los 5 minutos de gracia
                if (now()->greaterThan($shift->last_status_change_at)) {
                    $consumedSeconds = $shift->last_status_change_at->diffInSeconds(now());
                    $shift->lunch_seconds_left = max(0, $shift->lunch_seconds_left - $consumedSeconds);
                }
            }

            $shift->update([
                'current_status' => 'ONLINE',
                'break_reason' => null,
                'lunch_seconds_left' => $shift->lunch_seconds_left,
                'last_status_change_at' => now(),
                'last_action_at' => now() // <-- LOS 10 SEGUNDOS DE DELAY AL VOLVER A PISO
            ]);

            ShiftStatusLog::create([
                'daily_shift_id' => $shift->id,
                'previous_status' => $previousStatus,
                'new_status' => 'ONLINE',
                'changed_at' => now(),
            ]);
        }
        return back();
    }

    public function finishService(Request $request)
    {
        $request->validate(['shift_id' => 'required|exists:daily_shifts,id']);

        DB::transaction(function () use ($request) {
            $shift = DailyShift::lockForUpdate()->find($request->shift_id);

            $client = SalesQueue::where('assigned_shift_id', $shift->id)
                ->where('status', 'SERVING')
                ->first();

            if ($client) {
                // 1. Detenemos el cronómetro del cliente (La venta ya contó para sus métricas)
                $client->update(['status' => 'COMPLETED', 'completed_at' => now()]);
                $shift->increment('customers_served_count');
                
                // 2. Pasamos al vendedor a estado RATING (La cola lo ignorará temporalmente)
                $shift->update([
                    'current_status' => 'RATING',
                    'last_action_at' => now()
                ]);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Venta finalizada, pasando a calificación.']);
        }
        return back()->with('success', 'Venta finalizada');
    }

    public function submitRating(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:daily_shifts,id',
            'queue_id' => 'required|exists:sales_queue,id',
            'stars' => 'nullable|integer|min:0|max:5',
            'tags' => 'nullable|array',
            'comments' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {
            // Solo guardamos si realmente mandó estrellas (si omitió, el valor es 0)
            if ($request->has('stars') && $request->stars > 0) {
                \App\Models\SaleRating::create([
                    'sales_queue_id' => $request->queue_id,
                    'rater_type' => 'SELLER',
                    'stars' => $request->stars,
                    'tags' => $request->tags ?? [], // Laravel lo convertirá a JSON por el cast en el modelo
                    'comments' => $request->comments
                ]);
            }

            // Liberamos al vendedor para que reciba a su siguiente cliente
            $shift = DailyShift::find($request->shift_id);
            if ($shift && $shift->current_status === 'RATING') {
                $shift->update([
                    'current_status' => 'ONLINE',
                    'last_action_at' => now()
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function getRetentionList(Request $request)
    {
        // CORRECCIÓN: Filtramos para obtener solo los clientes de la última hora y media (90 minutos)
        $recentCompleted = SalesQueue::with('assignedShift.employee')
            ->where('status', 'COMPLETED')
            ->where('service_type', 'SALES')
            ->where('completed_at', '>=', now()->subMinutes(90))
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        $availableShifts = DailyShift::with('employee')
            ->where('work_date', today())
            ->where('current_status', 'ONLINE')
            ->get()
            ->filter(function ($shift) {
                return !SalesQueue::where('assigned_shift_id', $shift->id)
                    ->where('status', 'SERVING')
                    ->exists();
            })->values();

        return response()->json([
            'clients' => $recentCompleted,
            'available_sellers' => $availableShifts
        ]);
    }

    public function reassignRetention(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:sales_queue,id',
            'shift_id' => 'required|exists:daily_shifts,id'
        ]);

        $oldQueue = SalesQueue::find($request->queue_id);

        // LIMPIEZA Y PROTECCIÓN DEL TURNO: Aseguramos que no se duplique el -R y no exceda la BD
        $baseTurn = str_replace('-R', '', $oldQueue->turn_number);
        $newTurnNumber = !empty($baseTurn) ? substr($baseTurn, 0, 7) . '-R' : 'RET-R';

        SalesQueue::create([
            'customer_id' => $oldQueue->customer_id,
            'client_name' => $oldQueue->client_name,
            'client_type' => $oldQueue->client_type,
            'has_disability' => $oldQueue->has_disability,
            'service_type' => $oldQueue->service_type,
            'turn_number' => $newTurnNumber, // <--- Aplicamos el turno corregido
            'source' => 'MANUAL_KIOSK',
            'status' => 'SERVING',
            'assigned_shift_id' => $request->shift_id,
            'queued_at' => now(),
            'started_serving_at' => now()
        ]);

        DailyShift::find($request->shift_id)->update(['last_action_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function getSellersList()
    {
        return Employee::sellers()->with(['todayShift'])->get();
    }

    private function runMatchmaker()
    {
        // 1. Candado Atómico: Previene la condición de carrera.
        // Si varias peticiones llegan al mismo tiempo, solo una adquiere el bloqueo.
        $lock = Cache::lock('matchmaker_lock', 5);

        if (!$lock->get()) {
            return; // Otro proceso ya está emparejando, abortamos silenciosamente.
        }

        try {
            DB::transaction(function () {
                $waitingClients = SalesQueue::waiting()->sales()->count();
                if ($waitingClients === 0) return;

                // 2. Optimización (Escalabilidad): Usamos whereNotExists directo en SQL 
                // en lugar de iterar con Laravel, evitando el problema de consultas N+1.
                $availableShifts = DailyShift::where('work_date', today())
                    ->where('current_status', 'ONLINE')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('sales_queue')
                            ->whereColumn('sales_queue.assigned_shift_id', 'daily_shifts.id')
                            ->where('sales_queue.status', 'SERVING');
                    })
                    ->lockForUpdate() // Bloqueo de fila para mayor seguridad
                    ->get();

                $freeShifts = $availableShifts->filter(function ($shift) {
                    $cooldownPassed = true;
                    if (!empty($shift->last_action_at)) {
                        try {
                            $cooldownPassed = \Carbon\Carbon::parse($shift->last_action_at)->diffInSeconds(now()) >= 10;
                        } catch (\Exception $e) {
                            $cooldownPassed = true;
                        }
                    }

                    return $cooldownPassed;
                });

                if ($freeShifts->isEmpty()) return;

                $freeShifts = $freeShifts->sortBy(function ($shift) {
                    return $shift->last_action_at ?? '2000-01-01 00:00:00';
                });

                foreach ($freeShifts as $shift) {
                    $nextClient = SalesQueue::waiting()->sales()->lockForUpdate()->first();
                    if ($nextClient) {
                        $nextClient->update([
                            'status' => 'SERVING',
                            'assigned_shift_id' => $shift->id,
                            'started_serving_at' => now(),
                        ]);
                        
                        // Refrescamos last_action_at para reiniciar su estado de actividad
                        $shift->update([
                            'flagged_as_idle' => false,
                            'last_action_at' => now() 
                        ]);
                    } else {
                        break;
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error crítico en el Matchmaker: ' . $e->getMessage());
        } finally {
            // 3. Siempre liberamos el candado para que la siguiente ronda pueda ejecutarse
            $lock->release();
        }
    }
}
