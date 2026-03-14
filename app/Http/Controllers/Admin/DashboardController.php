<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SalesQueue;
use App\Models\DailyShift;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Métricas de HOY
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->count();
        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])->whereIn('status', ['ABANDONED', 'CANCELED'])->count();
        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])->whereNotNull('started_serving_at')->whereNotNull('completed_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])->whereNotNull('queued_at')->whereNotNull('started_serving_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')->value('avg_time');

        // 2. Fila Actual (Solo los que están esperando)
        $currentQueue = SalesQueue::where('status', 'WAITING')
            ->orderByRaw("CASE WHEN client_type = 'VIP' THEN 1 WHEN has_disability = 1 THEN 2 ELSE 3 END")
            ->orderBy('queued_at', 'asc')
            ->get();

        return view('admin.dashboard', [ // O la ruta de tu vista correspondiente (ej. solo 'dashboard')
            'metrics' => [
                'total_served' => $totalServed,
                'total_abandoned' => $totalAbandoned,
                'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
                'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
            ],
            'currentQueue' => $currentQueue
        ]);
    }

    public function realTimeDashboard()
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        // 1. MÉTRICAS GLOBALES ACTUALIZADAS
        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->count();
        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])->whereIn('status', ['ABANDONED', 'CANCELED'])->count();
        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])->whereNotNull('started_serving_at')->whereNotNull('completed_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])->whereNotNull('queued_at')->whereNotNull('started_serving_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')->value('avg_time');

        $metrics = [
            'total_served' => $totalServed,
            'total_abandoned' => $totalAbandoned,
            'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
            'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
        ];

        // 2. FILA ACTUAL (Actualizada)
        $currentQueue = SalesQueue::where('status', 'WAITING')
            ->orderByRaw("CASE WHEN client_type = 'VIP' THEN 1 WHEN has_disability = 1 THEN 2 ELSE 3 END")
            ->orderBy('queued_at', 'asc')
            ->get()
            ->map(function($q) {
                return [
                    'id' => $q->id,
                    'turn_number' => $q->turn_number,
                    'client_name' => $q->client_name,
                    'client_type' => $q->client_type,
                    'has_disability' => $q->has_disability,
                    'queued_at' => Carbon::parse($q->queued_at)->format('H:i')
                ];
            });

        // 3. MONITOR DE VENDEDORES (Con banderas VIP)
        $sellers = DailyShift::with(['employee', 'servedCustomers' => function($q) { $q->where('status', 'SERVING'); }])
            ->whereDate('work_date', today())
            ->get()
            ->map(function ($shift) {
                $currentClient = $shift->servedCustomers->first();
                $state = 'OFFLINE'; $stateStartedAt = null; $clientName = null; $breakReason = null;
                $clientType = null; $hasDisability = false;

                if ($currentClient && $currentClient->started_serving_at) { 
                    $state = 'SERVING'; 
                    $stateStartedAt = Carbon::parse($currentClient->started_serving_at)->timestamp * 1000; 
                    $clientName = $currentClient->client_name; 
                    $clientType = $currentClient->client_type;
                    $hasDisability = $currentClient->has_disability;
                } 
                elseif ($shift->current_status === 'BREAK' && $shift->last_status_change_at) { 
                    $state = 'BREAK'; 
                    $stateStartedAt = Carbon::parse($shift->last_status_change_at)->timestamp * 1000; 
                    $breakReason = $shift->break_reason ?? 'General';
                } 
                elseif ($shift->current_status === 'ONLINE' && $shift->last_status_change_at) { 
                    $state = 'ONLINE'; 
                    $stateStartedAt = Carbon::parse($shift->last_status_change_at)->timestamp * 1000; 
                }

                return [
                    'id' => $shift->employee->id, 
                    'name' => $shift->employee->full_name ?? 'Vendedor', 
                    'state' => $state, 
                    'state_started_at' => $stateStartedAt, 
                    'client_name' => $clientName, 
                    'client_type' => $clientType,
                    'has_disability' => $hasDisability,
                    'break_reason' => $breakReason, 
                    'sales_today' => $shift->customers_served_count
                ];
            });

        return response()->json([
            'metrics' => $metrics,
            'queue' => $currentQueue,
            'sellers' => $sellers
        ]);
    }

    private function formatSeconds($totalSeconds)
    {
        if (!$totalSeconds || $totalSeconds <= 0) return '00:00';
        $hours = floor($totalSeconds / 3600); 
        $minutes = floor(($totalSeconds % 3600) / 60); 
        $seconds = round($totalSeconds % 60);
        
        $result = '';
        if ($hours > 0) $result .= $hours . 'h ';
        if ($minutes > 0 || $hours > 0) $result .= $minutes . 'm ';
        if ($seconds > 0 || ($hours == 0 && $minutes == 0)) $result .= $seconds . 's';
        return trim($result);
    }

}