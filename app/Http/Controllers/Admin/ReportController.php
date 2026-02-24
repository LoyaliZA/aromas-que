<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SalesQueue;
use App\Models\Employee;
use App\Models\DailyShift;
use App\Models\ShiftStatusLog;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. CONFIGURACIÓN DE FECHAS Y PESTAÑAS
        $period = $request->input('period', 'today');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $activeTab = $request->input('tab', 'dashboard');
        $selectedEmployeeId = $request->input('employee_id');

        if ($period === 'today') {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        } elseif ($period === 'week') {
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
        } elseif ($period === 'month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
            $period = 'today';
        }

        // Variable para saber si estamos consultando 1 solo día o varios
        $isSingleDay = $start->isSameDay($end);

        // ==========================================
        // SECCIÓN A: MÉTRICAS GENERALES (DASHBOARD)
        // ==========================================
        
        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])
            ->where('status', 'COMPLETED')
            ->count();

        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])
            ->whereIn('status', ['ABANDONED', 'CANCELED'])
            ->count();

        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])
            ->whereNotNull('started_serving_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')
            ->value('avg_time');

        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])
            ->whereNotNull('queued_at')
            ->whereNotNull('started_serving_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')
            ->value('avg_time');

        $employeesMetrics = Employee::sellers()
            ->get()
            ->map(function ($employee) use ($start, $end) {
                $servedCount = SalesQueue::whereHas('assignedShift', function($query) use ($employee) {
                        $query->where('employee_id', $employee->id);
                    })
                    ->whereBetween('completed_at', [$start, $end])
                    ->where('status', 'COMPLETED')
                    ->count();

                $totalBreakSeconds = 0;
                $shifts = DailyShift::where('employee_id', $employee->id)
                    ->whereBetween('work_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                    ->with(['statusLogs' => function($q) { $q->orderBy('changed_at', 'asc'); }])
                    ->get();

                foreach ($shifts as $shift) {
                    $breakStart = null;
                    foreach ($shift->statusLogs as $log) {
                        if ($log->new_status === 'BREAK') {
                            $breakStart = Carbon::parse($log->changed_at);
                        } elseif ($breakStart && $log->previous_status === 'BREAK') {
                            $totalBreakSeconds += $breakStart->diffInSeconds(Carbon::parse($log->changed_at));
                            $breakStart = null;
                        }
                    }
                }

                return [
                    'id' => $employee->id,
                    'name' => $employee->full_name ?? 'Desconocido', 
                    'served' => $servedCount,
                    'formatted_break_time' => $this->formatSeconds($totalBreakSeconds),
                ];
            });

        // 4. PREPARACIÓN DE DATOS PARA GRÁFICAS (JSON) CON GRANULARIDAD DINÁMICA
        $chartTitle = $isSingleDay ? 'Flujo de Atención por Hora' : 'Flujo de Atención por Día';
        $chartData = ['labels' => [], 'data' => []];

        if ($isSingleDay) {
            // SI ES UN DÍA: Agrupamos por hora (9am a 9pm)
            $salesByHour = SalesQueue::whereBetween('completed_at', [$start, $end])
                ->where('status', 'COMPLETED')
                ->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->pluck('count', 'hour')
                ->toArray();
                
            for ($i = 9; $i <= 21; $i++) { 
                $chartData['labels'][] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $chartData['data'][] = $salesByHour[$i] ?? 0;
            }
        } else {
            // SI SON VARIOS DÍAS: Agrupamos por fecha
            $salesByDate = SalesQueue::whereBetween('completed_at', [$start, $end])
                ->where('status', 'COMPLETED')
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();
                
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                $dateStr = $currentDate->format('Y-m-d');
                $chartData['labels'][] = $currentDate->format('d/m'); // Ej: 24/02
                $chartData['data'][] = $salesByDate[$dateStr] ?? 0;
                $currentDate->addDay();
            }
        }

        // ==========================================
        // SECCIÓN B: MÉTRICAS INDIVIDUALES (RENDIMIENTO)
        // ==========================================
        
        $employeesList = Employee::sellers()->get(); 
        $empKpis = ['served' => 0, 'formatted_avg_time' => '0s', 'extensions' => 0, 'abandoned' => 0];
        $empPerformanceData = [];
        $empBreaksData = [];
        $empClientsPaginated = null;

        if ($selectedEmployeeId && $activeTab === 'performance') {
            $empSalesQuery = SalesQueue::whereHas('assignedShift', function($q) use ($selectedEmployeeId) {
                $q->where('employee_id', $selectedEmployeeId);
            })->whereBetween('queued_at', [$start, $end]);

            $empKpis['served'] = (clone $empSalesQuery)->where('status', 'COMPLETED')->count();
            $empKpis['abandoned'] = (clone $empSalesQuery)->whereIn('status', ['ABANDONED', 'CANCELED'])->count();
            $empKpis['extensions'] = (clone $empSalesQuery)->sum('extension_count');
            
            $empAvgSeconds = (clone $empSalesQuery)->where('status', 'COMPLETED')
                ->whereNotNull('started_serving_at')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')
                ->value('avg_time');
            
            $empKpis['formatted_avg_time'] = $this->formatSeconds($empAvgSeconds ?? 0);

            $completedSales = (clone $empSalesQuery)->where('status', 'COMPLETED')
                ->whereNotNull('queued_at')
                ->whereNotNull('started_serving_at')
                ->whereNotNull('completed_at')
                ->orderBy('queued_at', 'asc')
                ->get();

            $empPerformanceData = $completedSales->map(function($sale) {
                return [
                    'queued_at' => Carbon::parse($sale->queued_at)->toIso8601String(),
                    'wait_time' => round(Carbon::parse($sale->queued_at)->diffInSeconds(Carbon::parse($sale->started_serving_at)) / 60, 2),
                    'service_time' => round(Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at)) / 60, 2),
                ];
            });

            $empClientsPaginated = (clone $empSalesQuery)->where('status', 'COMPLETED')
                ->orderBy('completed_at', 'desc')
                ->paginate(10)
                ->withQueryString()
                ->through(function ($client) {
                    $wait = $client->queued_at && $client->started_serving_at ? Carbon::parse($client->queued_at)->diffInSeconds(Carbon::parse($client->started_serving_at)) : 0;
                    $serve = $client->started_serving_at && $client->completed_at ? Carbon::parse($client->started_serving_at)->diffInSeconds(Carbon::parse($client->completed_at)) : 0;
                    $client->formatted_wait = $this->formatSeconds($wait);
                    $client->formatted_serve = $this->formatSeconds($serve);
                    return $client;
                });

            $empBreaksData = DailyShift::where('employee_id', $selectedEmployeeId)
                ->whereBetween('work_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
                ->whereNotNull('break_reason')
                ->selectRaw('break_reason, COUNT(*) as total')
                ->groupBy('break_reason')
                ->get();
        }

        return view('admin.reports.index', [
            'period' => $period,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'activeTab' => $activeTab,
            'is_single_day' => $isSingleDay, // <-- Mandamos el dato a la vista
            'chart_title' => $chartTitle,    // <-- Mandamos el título a la vista
            
            'metrics' => [
                'total_served' => $totalServed,
                'total_abandoned' => $totalAbandoned,
                'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
                'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
            ],
            'employees_metrics' => $employeesMetrics,
            'chart_data' => $chartData,

            'employees' => $employeesList,
            'selectedEmployeeId' => $selectedEmployeeId,
            'empKpis' => $empKpis,
            'empPerformanceData' => $empPerformanceData,
            'empBreaksData' => $empBreaksData,
            'empClientsPaginated' => $empClientsPaginated,
        ]);
    }

    private function formatSeconds($totalSeconds)
    {
        if (!$totalSeconds || $totalSeconds <= 0) return '0s';
        
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = round($totalSeconds % 60);

        $result = '';
        if ($hours > 0) {
            $result .= $hours . 'h ';
        }
        if ($minutes > 0 || $hours > 0) {
            $result .= $minutes . 'm ';
        }
        if ($seconds > 0 || ($hours == 0 && $minutes == 0)) {
            $result .= $seconds . 's';
        }

        return trim($result);
    }
}