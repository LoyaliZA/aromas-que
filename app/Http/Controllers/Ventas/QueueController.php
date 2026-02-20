<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\DailyShift;
use App\Models\SalesQueue;
use Illuminate\Support\Facades\DB;

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
        // 1. Ejecutar Matchmaker
        $this->runMatchmaker();

        // 2. Obtener datos actualizados
        $sellers = $this->getSellersList();
        $clientsWaiting = SalesQueue::waiting()->sales()->count();

        // 3. DETECTAR ASIGNACIÓN RECIENTE (Para la Mega Notificación)
        // Buscamos si alguien empezó a ser atendido en los últimos 4 segundos
        $recentAssignment = SalesQueue::where('status', 'SERVING')
            ->where('started_serving_at', '>=', now()->subSeconds(4))
            ->with('assignedShift.employee') // Traemos datos del vendedor
            ->first();

        $alertData = null;
        if ($recentAssignment && $recentAssignment->assignedShift) {
            $alertData = [
                'client' => $recentAssignment->client_name,
                'seller' => $recentAssignment->assignedShift->employee->full_name,
                'folio'  => $recentAssignment->id
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

        if ($shift->current_status === 'ONLINE') {
            $reason = $request->reason ?? 'GENERAL';
            $shift->update(['current_status' => 'BREAK', 'break_reason' => $reason]);
        } elseif ($shift->current_status === 'BREAK') {
            $shift->update(['current_status' => 'ONLINE', 'break_reason' => null, 'last_status_change_at' => now()]);
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
                $client->update(['status' => 'COMPLETED', 'completed_at' => now()]);
                $shift->increment('customers_served_count');
                $shift->update([
                    'last_status_change_at' => now(),
                    'last_action_at' => now()
                ]);
            }
        });

        return back()->with('success', 'Venta finalizada');
    }

    private function getSellersList()
    {
        // Traemos empleados marcados para venta
        return Employee::sellers()->with(['todayShift'])->get();
    }

    private function runMatchmaker()
    {
        // (Tu lógica de matchmaker existente...)
        $waitingClients = SalesQueue::waiting()->sales()->count();
        if ($waitingClients === 0) return;

        $availableShifts = DailyShift::where('work_date', today())
            ->where('current_status', 'ONLINE')
            ->where('flagged_as_idle', false)
            ->get();

        $freeShifts = $availableShifts->filter(function ($shift) {
            return !SalesQueue::where('assigned_shift_id', $shift->id)
                              ->where('status', 'SERVING')
                              ->exists();
        });

        if ($freeShifts->isEmpty()) return;

        $totalSales = $availableShifts->sum('customers_served_count');
        
        if ($totalSales == 0) {
            $freeShifts = $freeShifts->shuffle();
        } else {
            $freeShifts = $freeShifts->sortBy('last_status_change_at');
        }

        foreach ($freeShifts as $shift) {
            $nextClient = SalesQueue::waiting()->sales()->lockForUpdate()->first();
            if ($nextClient) {
                $nextClient->update([
                    'status' => 'SERVING',
                    'assigned_shift_id' => $shift->id,
                    'started_serving_at' => now(),
                ]);
            } else {
                break;
            }
        }
    }
    
}