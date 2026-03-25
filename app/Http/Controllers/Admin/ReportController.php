<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\SalesQueue;
use App\Models\Employee;
use App\Models\DailyShift;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    // Diccionarios de traducción estáticos
    private $reasonsDict = [
        'LUNCH' => 'Comida',
        'BATHROOM' => 'Baño',
        'ERRAND' => 'Mandado',
        'PACKAGING' => 'Empaque',
        'GENERAL' => 'General / Otros'
    ];

    private $statusDict = [
        'ONLINE' => 'DISPONIBLE',
        'BREAK' => 'PAUSA',
        'OFFLINE' => 'INACTIVO',
        'SERVING' => 'ATENDIENDO'
    ];

    public function index(Request $request)
    {
        $period = $request->input('period', 'today');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $activeTab = $request->input('tab', $request->has('period') ? 'general' : 'realtime'); 
        $selectedEmployeeId = $request->input('employee_id');

        if ($period === 'today') {
            $start = Carbon::today()->startOfDay(); $end = Carbon::today()->endOfDay();
        } elseif ($period === 'week') {
            $start = Carbon::now()->startOfWeek(); $end = Carbon::now()->endOfWeek();
        } elseif ($period === 'month') {
            $start = Carbon::now()->startOfMonth(); $end = Carbon::now()->endOfMonth();
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay(); $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $start = Carbon::today()->startOfDay(); $end = Carbon::today()->endOfDay();
            $period = 'today';
        }

        $isSingleDay = $start->isSameDay($end);

        // --- MÉTRICAS GLOBALES ---
        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->count();
        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])->whereIn('status', ['ABANDONED', 'CANCELED'])->count();
        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])->whereNotNull('started_serving_at')->whereNotNull('completed_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])->whereNotNull('queued_at')->whereNotNull('started_serving_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')->value('avg_time');

        // --- DESEMPEÑO EMPLEADOS (CON CALIFICACIONES) ---
        $employeesList = Employee::sellers()->get();
        $employeesMetrics = $employeesList->map(function ($employee) use ($start, $end) {
            $sales = SalesQueue::with('ratings')
                ->whereHas('assignedShift', function($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })->whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->get();
            
            $servedCount = $sales->count();
            $avgEmpServiceSeconds = $servedCount > 0 ? ($sales->reduce(function ($carry, $sale) { return $carry + Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at)); }, 0) / $servedCount) : 0;

            // Extraer calificaciones promedio del cliente
            $clientRatings = $sales->flatMap->ratings->where('rater_type', 'CLIENT');
            $avgStars = $clientRatings->count() > 0 ? round($clientRatings->avg('stars'), 1) : 0;

            return [
                'name' => $employee->full_name ?? 'Desconocido', 
                'served' => $servedCount,
                'formatted_avg_service' => $this->formatSeconds($avgEmpServiceSeconds),
                'formatted_break_time' => $this->calculateEmployeeBreaks($employee->id, $start, $end)['total_formatted'],
                'avg_stars' => $avgStars // <--- NUEVO KPI
            ];
        });

        // --- GRÁFICA (Ajustada hasta las 19:00 / 7:00 PM) ---
        $chartTitle = $isSingleDay ? 'Flujo de Atención por Hora' : 'Flujo de Atención por Día';
        $chartData = ['labels' => [], 'data' => []];
        if ($isSingleDay) {
            $salesByHour = SalesQueue::whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')->groupBy('hour')->pluck('count', 'hour')->toArray();
            // Límite ajustado de 9 a 19 (7 PM)
            for ($i = 9; $i <= 19; $i++) { 
                $chartData['labels'][] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00'; 
                $chartData['data'][] = $salesByHour[$i] ?? 0; 
            }
        } else {
            $salesByDate = SalesQueue::whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->selectRaw('DATE(completed_at) as date, COUNT(*) as count')->groupBy('date')->pluck('count', 'date')->toArray();
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) { 
                $chartData['labels'][] = $currentDate->format('d/m'); 
                $chartData['data'][] = $salesByDate[$currentDate->format('Y-m-d')] ?? 0; 
                $currentDate->addDay(); 
            }
        }

        // --- TABLAS DETALLADAS EXISTENTES ---
        $detailedClients = SalesQueue::with(['assignedShift.employee', 'ratings'])->whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->orderBy('completed_at', 'desc')->paginate(15, ['*'], 'clients_page')->appends(request()->query());
        $detailedClients->getCollection()->transform(function ($client) {
            $client->formatted_wait = $this->formatSeconds($client->queued_at && $client->started_serving_at ? Carbon::parse($client->queued_at)->diffInSeconds(Carbon::parse($client->started_serving_at)) : 0);
            $client->formatted_serve = $this->formatSeconds($client->started_serving_at && $client->completed_at ? Carbon::parse($client->started_serving_at)->diffInSeconds(Carbon::parse($client->completed_at)) : 0);
            return $client;
        });

        $detailedAbandoned = SalesQueue::with('abandonmentReason')->whereBetween('queued_at', [$start, $end])->whereIn('status', ['ABANDONED', 'CANCELED'])->orderBy('queued_at', 'desc')->paginate(15, ['*'], 'abandoned_page')->appends(request()->query());

        // --- NUEVAS PESTAÑAS: CALIFICACIONES MASIVAS ---
        $clientRatings = null;
        $sellerRatings = null;
        
        // Obtenemos si el usuario quiere ordenar por mejor o peor
        $sortOrder = $request->input('sort', 'desc'); 

        if ($activeTab === 'client_ratings') {
            $clientRatings = \App\Models\SaleRating::with(['salesQueue.assignedShift.employee'])
                ->where('rater_type', 'CLIENT')
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('stars', $sortOrder) // Ordenar de mejor a peor o viceversa
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'cr_page')->appends(request()->query());
        }

        if ($activeTab === 'seller_ratings') {
            $sellerRatings = \App\Models\SaleRating::with(['salesQueue.assignedShift.employee'])
                ->where('rater_type', 'SELLER')
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('stars', $sortOrder)
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'sr_page')->appends(request()->query());
        }

        // --- RENDIMIENTO INDIVIDUAL ---
        $empData = null;
        if ($selectedEmployeeId && $activeTab === 'performance') {
            $empSalesQuery = SalesQueue::with('ratings')->whereHas('assignedShift', function($q) use ($selectedEmployeeId) {
                $q->where('employee_id', $selectedEmployeeId);
            })->whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED');

            $empServed = (clone $empSalesQuery)->count();
            $empAvgSeconds = (clone $empSalesQuery)->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
            
            // Calculamos el promedio de estrellas que le dieron al vendedor
            $empAvgStars = \App\Models\SaleRating::where('rater_type', 'CLIENT')
                ->whereIn('sales_queue_id', (clone $empSalesQuery)->select('id'))
                ->avg('stars');
            
            $breakAnalysis = $this->calculateEmployeeBreaks($selectedEmployeeId, $start, $end);

            $empClientsPaginated = (clone $empSalesQuery)->orderBy('completed_at', 'desc')->paginate(10, ['*'], 'emp_page')->appends(request()->query());
            $empClientsPaginated->getCollection()->transform(function ($client) {
                $client->formatted_wait = $this->formatSeconds(Carbon::parse($client->queued_at)->diffInSeconds(Carbon::parse($client->started_serving_at)));
                $client->formatted_serve = $this->formatSeconds(Carbon::parse($client->started_serving_at)->diffInSeconds(Carbon::parse($client->completed_at)));
                $client->is_reattended = str_ends_with($client->turn_number, '-R');
                return $client;
            });

            $empData = [
                'employee' => Employee::find($selectedEmployeeId),
                'kpis' => [
                    'served' => $empServed,
                    'avg_time' => $this->formatSeconds($empAvgSeconds ?? 0),
                    'total_break' => $breakAnalysis['total_formatted'],
                    'total_available' => $breakAnalysis['total_available_formatted'],
                    'avg_stars' => $empAvgStars ? round($empAvgStars, 1) : 0 // <--- NUEVO KPI INDIVIDUAL
                ],
                'daily_breaks' => $breakAnalysis['daily_breaks'],
                'timeline' => $breakAnalysis['timeline'],
                'clients' => $empClientsPaginated
            ];
        }

        return view('admin.reports.index', [
            'period' => $period, 'start_date' => $start->format('Y-m-d'), 'end_date' => $end->format('Y-m-d'),
            'activeTab' => $activeTab, 'is_single_day' => $isSingleDay, 'chart_title' => $chartTitle,
            'metrics' => [
                'total_served' => $totalServed, 'total_abandoned' => $totalAbandoned,
                'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
                'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
            ],
            'employees_metrics' => $employeesMetrics,
            'chart_data' => $chartData,
            'detailedClients' => $detailedClients,
            'detailedAbandoned' => $detailedAbandoned,
            'employeesList' => $employeesList,
            'selectedEmployeeId' => $selectedEmployeeId,
            'empData' => $empData,
            // --- ¡AQUÍ ESTÁ LA MAGIA QUE FALTABA! ---
            'clientRatings' => $clientRatings,
            'sellerRatings' => $sellerRatings
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'clients'); 
        $format = $request->input('format', 'csv'); 
        $start = Carbon::parse($request->input('start_date') ?? today())->startOfDay();
        $end = Carbon::parse($request->input('end_date') ?? today())->endOfDay();
        $empId = $request->input('employee_id');

        $headers = []; $rows = []; $employee = null; $stats = []; $timeline = []; $dailyBreaks = [];

        if ($type === 'clients') {
            $headers = ['Turno', 'Cliente', 'Tipo', 'Vendedor', 'Tiempo Espera', 'Tiempo Atención', 'Estado', 'Fecha'];
            $data = SalesQueue::with(['assignedShift.employee'])->whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->orderBy('completed_at', 'desc')->get();
            foreach($data as $d) {
                $wait = $this->formatSeconds(Carbon::parse($d->queued_at)->diffInSeconds(Carbon::parse($d->started_serving_at)));
                $serve = $this->formatSeconds(Carbon::parse($d->started_serving_at)->diffInSeconds(Carbon::parse($d->completed_at)));
                $rows[] = [$d->turn_number, $d->client_name, $d->client_type, $d->assignedShift->employee->full_name ?? 'N/A', $wait, $serve, str_ends_with($d->turn_number, '-R') ? 'RE-ATENDIDO' : 'NORMAL', Carbon::parse($d->completed_at)->format('d/m/Y H:i:s')];
            }
        } elseif ($type === 'abandoned') {
            $headers = ['Turno', 'Cliente', 'Tipo', 'Fecha/Hora', 'Motivo de Abandono'];
            $data = SalesQueue::with('abandonmentReason')->whereBetween('queued_at', [$start, $end])->whereIn('status', ['ABANDONED', 'CANCELED'])->orderBy('queued_at', 'desc')->get();
            foreach($data as $d) {
                $motivo = !empty($d->custom_abandonment_reason) ? $d->custom_abandonment_reason : ($d->abandonment_reason_id ? $d->abandonmentReason->reason : 'Inactividad');
                $rows[] = [$d->turn_number, $d->client_name, $d->client_type, Carbon::parse($d->queued_at)->format('d/m/Y H:i:s'), $motivo];
            }
        } elseif ($type === 'employee' && $empId) {
            $employee = Employee::find($empId);
            $headers = ['Turno', 'Cliente', 'Tipo Cliente', 'Tiempo Atención', 'Estado', 'Fecha/Hora'];
            $data = SalesQueue::whereHas('assignedShift', function($q) use ($empId) { $q->where('employee_id', $empId); })->whereBetween('completed_at', [$start, $end])->where('status', 'COMPLETED')->orderBy('completed_at', 'desc')->get();
            
            $servedCount = $data->count();
            $avgEmpServiceSeconds = $servedCount > 0 ? ($data->reduce(function ($carry, $sale) { return $carry + Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at)); }, 0) / $servedCount) : 0;
            $breakAnalysis = $this->calculateEmployeeBreaks($empId, $start, $end);
            
            $stats = ['served' => $servedCount, 'avg_time' => $this->formatSeconds($avgEmpServiceSeconds), 'break_time' => $breakAnalysis['total_formatted']];
            $timeline = array_reverse($breakAnalysis['timeline']); 
            $dailyBreaks = $breakAnalysis['daily_breaks']; // PASAMOS EL DESGLOSE AL PDF

            foreach($data as $d) {
                $serve = $this->formatSeconds(Carbon::parse($d->started_serving_at)->diffInSeconds(Carbon::parse($d->completed_at)));
                $rows[] = [$d->turn_number, $d->client_name, $d->client_type, $serve, str_ends_with($d->turn_number, '-R') ? 'RE-ATENDIDO' : 'NORMAL', Carbon::parse($d->completed_at)->format('d/m/Y H:i:s')];
            }
        }

        $fileName = 'Reporte_TERA_' . ucfirst($type) . '_' . now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf', compact('type', 'start', 'end', 'headers', 'rows', 'employee', 'stats', 'timeline', 'dailyBreaks'));
            return $pdf->download($fileName . '.pdf');
        }

        $callback = function() use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF))); 
            fputcsv($file, $headers);
            foreach ($rows as $row) { fputcsv($file, $row); }
            fclose($file);
        };
        return response()->stream($callback, 200, ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName.csv"]);
    }

    // --- CÁLCULO DE PAUSAS Y TIEMPO DISPONIBLE (TRADUCIDO Y AGRUPADO POR DÍA) ---
    private function calculateEmployeeBreaks($employeeId, $start, $end)
    {
        $shifts = DailyShift::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->with(['statusLogs' => function($q) { $q->orderBy('changed_at', 'asc'); }])
            ->get();
        
        $totalBreakSeconds = 0;
        $totalAvailableSeconds = 0;
        $dailySeconds = []; 
        $timeline = [];

        foreach ($shifts as $shift) {
            $breakStart = null;
            $onlineStart = null;
            $currentReason = null;
            
            // Aseguramos que el día exista en el arreglo
            $dateStr = Carbon::parse($shift->work_date)->format('Y-m-d');
            if (!isset($dailySeconds[$dateStr])) {
                $dailySeconds[$dateStr] = ['LUNCH' => 0, 'BATHROOM' => 0, 'ERRAND' => 0, 'PACKAGING' => 0, 'GENERAL' => 0, 'AVAILABLE' => 0];
            }

            foreach ($shift->statusLogs as $log) {
                $transStatus = $this->statusDict[$log->new_status] ?? $log->new_status;

                // --- 1. Lógica para el Timeline Visual ---
                if ($log->new_status === 'BREAK' || $log->previous_status === 'BREAK') {
                    $statusLabel = $transStatus;
                    if ($log->new_status === 'BREAK') {
                        $reasonTrans = $this->reasonsDict[$log->reason] ?? ($log->reason ?? 'General'); 
                        $statusLabel .= ' (' . $reasonTrans . ')';
                    }

                    $timeline[] = [
                        'status' => $statusLabel,
                        'time' => Carbon::parse($log->changed_at)->format('H:i:s'),
                        'date' => Carbon::parse($log->changed_at)->format('d/m/Y'),
                        'color' => $log->new_status === 'BREAK' ? 'text-yellow-500' : 'text-green-400'
                    ];
                }

                // --- 2. Lógica Matemática para Acumular Segundos ---
                $logTime = Carbon::parse($log->changed_at);

                if ($log->new_status === 'BREAK') {
                    $breakStart = $logTime;
                    $currentReason = $log->reason ?? 'GENERAL'; 
                    // Si estaba online, sumamos ese bloque al tiempo disponible
                    if ($onlineStart) {
                        $dailySeconds[$dateStr]['AVAILABLE'] += $onlineStart->diffInSeconds($logTime);
                        $onlineStart = null;
                    }
                } elseif ($log->new_status === 'ONLINE') {
                    $onlineStart = $logTime;
                    // Si estaba en break, sumamos el bloque al break
                    if ($breakStart && $log->previous_status === 'BREAK') {
                        $duration = $breakStart->diffInSeconds($logTime);
                        $totalBreakSeconds += $duration;
                        $dailySeconds[$dateStr][$currentReason] += $duration;
                        $breakStart = null;
                    }
                } elseif ($log->new_status === 'OFFLINE') {
                    if ($onlineStart) {
                        $dailySeconds[$dateStr]['AVAILABLE'] += $onlineStart->diffInSeconds($logTime);
                        $onlineStart = null;
                    }
                    if ($breakStart && $log->previous_status === 'BREAK') {
                        $duration = $breakStart->diffInSeconds($logTime);
                        $totalBreakSeconds += $duration;
                        $dailySeconds[$dateStr][$currentReason] += $duration;
                        $breakStart = null;
                    }
                }
            }

            // Si el turno sigue abierto (hoy), contar hasta el minuto actual
            if ($shift->work_date == today()->format('Y-m-d')) {
                if ($onlineStart && $shift->current_status === 'ONLINE') {
                    $dailySeconds[$dateStr]['AVAILABLE'] += $onlineStart->diffInSeconds(now());
                }
                if ($breakStart && $shift->current_status === 'BREAK') {
                    $duration = $breakStart->diffInSeconds(now());
                    $totalBreakSeconds += $duration;
                    $dailySeconds[$dateStr][$currentReason] += $duration;
                }
            }

            // --- 3. Restar el tiempo "Atendiendo" del tiempo Disponible ---
            $servingSeconds = \App\Models\SalesQueue::where('assigned_shift_id', $shift->id)
                ->where('status', 'COMPLETED')
                ->whereNotNull('started_serving_at')
                ->whereNotNull('completed_at')
                ->get()
                ->reduce(function ($carry, $sale) { 
                    return $carry + Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at)); 
                }, 0);

            $dailySeconds[$dateStr]['AVAILABLE'] -= $servingSeconds;
            if ($dailySeconds[$dateStr]['AVAILABLE'] < 0) $dailySeconds[$dateStr]['AVAILABLE'] = 0;

            $totalAvailableSeconds += $dailySeconds[$dateStr]['AVAILABLE'];
        }

        // Formateamos las matrices para enviarlas a Blade
        $formattedDailyBreaks = [];
        foreach ($dailySeconds as $date => $reasonsArray) {
            $formattedDailyBreaks[$date] = [];
            foreach ($reasonsArray as $reason => $secs) {
                if ($reason === 'AVAILABLE') {
                    // Colocamos "Tiempo Disponible" al inicio del arreglo visual
                    $formattedDailyBreaks[$date] = ['Tiempo Disponible' => $this->formatSeconds($secs)] + $formattedDailyBreaks[$date];
                } else {
                    $reasonName = $this->reasonsDict[$reason] ?? $reason;
                    $formattedDailyBreaks[$date][$reasonName] = $this->formatSeconds($secs);
                }
            }
        }

        return [
            'total_formatted' => $this->formatSeconds($totalBreakSeconds),
            'total_available_formatted' => $this->formatSeconds($totalAvailableSeconds), // <-- Nuevo KPI Global
            'daily_breaks' => $formattedDailyBreaks,
            'timeline' => $timeline 
        ];
    }

    public function realTimeData() {
        $sellers = DailyShift::with([
            'employee', 
            'servedCustomers' => function($q) { 
                $q->where('status', 'SERVING'); 
            }
        ])
        ->whereDate('work_date', today())
        ->get()
        ->map(function ($shift) {
            $currentClient = $shift->servedCustomers->first();
            $state = 'OFFLINE'; 
            $stateStartedAt = null; 
            $clientName = null; 
            $breakReason = null;
            
            if ($currentClient && $currentClient->started_serving_at) { 
                $state = 'SERVING'; 
                $stateStartedAt = Carbon::parse($currentClient->started_serving_at)->timestamp * 1000; 
                $clientName = $currentClient->client_name; 
            } 
            elseif ($shift->current_status === 'BREAK' && $shift->last_status_change_at) { 
                $state = 'BREAK'; 
                $stateStartedAt = Carbon::parse($shift->last_status_change_at)->timestamp * 1000; 
                $breakReason = $this->reasonsDict[$shift->break_reason] ?? ($shift->break_reason ?? 'General'); 
            } 
            elseif ($shift->current_status === 'ONLINE' && $shift->last_status_change_at) { 
                $state = 'ONLINE'; 
                
                // Buscar a qué hora terminó su última venta de hoy
                $lastSale = \App\Models\SalesQueue::where('assigned_shift_id', $shift->id)
                    ->where('status', 'COMPLETED')
                    ->latest('completed_at')
                    ->first();
                    
                $statusChangeTime = Carbon::parse($shift->last_status_change_at);
                
                // Si la última venta terminó después de su último cambio de estado (ej. regresó de comer)
                if ($lastSale && $lastSale->completed_at) {
                    $lastSaleTime = Carbon::parse($lastSale->completed_at);
                    $stateStartedAt = $lastSaleTime->greaterThan($statusChangeTime) 
                        ? $lastSaleTime->timestamp * 1000 
                        : $statusChangeTime->timestamp * 1000;
                } else {
                    $stateStartedAt = $statusChangeTime->timestamp * 1000;
                }
            }
            
            return [
                'id' => $shift->employee->id, 
                'name' => $shift->employee->full_name ?? 'Vendedor', 
                'state' => $state, 
                'state_started_at' => $stateStartedAt, 
                'client_name' => $clientName, 
                'break_reason' => $breakReason, 
                'sales_today' => $shift->customers_served_count,
                // Agregamos la hora de creación del turno (hora de llegada)
                'shift_started_at' => $shift->created_at ? Carbon::parse($shift->created_at)->format('h:i A') : '--:--'
            ];
        });
        
        return response()->json(['sellers' => $sellers]);
    }

    private function formatSeconds($totalSeconds) {
        if (!$totalSeconds || $totalSeconds <= 0) return '00:00';
        $hours = floor($totalSeconds / 3600); $minutes = floor(($totalSeconds % 3600) / 60); $seconds = round($totalSeconds % 60);
        $result = '';
        if ($hours > 0) $result .= $hours . 'h ';
        if ($minutes > 0 || $hours > 0) $result .= $minutes . 'm ';
        if ($seconds > 0 || ($hours == 0 && $minutes == 0)) $result .= $seconds . 's';
        return trim($result);
    }
}