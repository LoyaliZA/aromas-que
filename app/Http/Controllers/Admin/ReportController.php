<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateEmployeePdfJob;
use App\Models\AttentionIncident;
use App\Models\BreakReason;
use App\Models\DailyShift;
use App\Models\Employee;
use App\Models\SaleRating;
use App\Models\SalesQueue;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    // Diccionarios de traducción estáticos
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

        $isSingleDay = $start->isSameDay($end);

        // --- MÉTRICAS GLOBALES ---
        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->count();
        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])->where(function ($q) {
            $q->withStatusCode('ABANDONED')->orWhere(fn ($q2) => $q2->withStatusCode('CANCELED'));
        })->count();
        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])->whereNotNull('started_serving_at')->whereNotNull('completed_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])->whereNotNull('queued_at')->whereNotNull('started_serving_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')->value('avg_time');
        $filterSeller = $request->input('seller_id_filter');

        $incidentsQuery = AttentionIncident::whereBetween('created_at', [$start, $end]);
        if (!empty($filterSeller)) {
            $incidentsQuery->where('employee_id', $filterSeller);
        }
        $totalIncidents = (clone $incidentsQuery)->count();
        $detailedIncidents = $incidentsQuery->with(['employee', 'salesQueue'])->orderBy('created_at', 'desc')->paginate(15, ['*'], 'incident_page')->appends(request()->query());

        // --- DESEMPEÑO EMPLEADOS (CON CALIFICACIONES) ---
        $employeesList = Employee::sellers()->get();
        $employeesMetrics = $employeesList->map(function ($employee) use ($start, $end) {
            $sales = SalesQueue::with('ratings')
                ->whereHas('assignedShift', function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })->whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->get();

            $servedCount = $sales->count();
            $avgEmpServiceSeconds = $servedCount > 0 ? ($sales->reduce(function ($carry, $sale) {
                return $carry + Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at));
            }, 0) / $servedCount) : 0;

            // Extraer calificaciones promedio del cliente
            $clientRatings = $sales->flatMap->ratings->where('rater_type', 'CLIENT');
            $avgStars = $clientRatings->count() > 0 ? round($clientRatings->avg('stars'), 1) : 0;

            return [
                'name' => $employee->full_name ?? 'Desconocido',
                'served' => $servedCount,
                'formatted_avg_service' => $this->formatSeconds($avgEmpServiceSeconds),
                'formatted_break_time' => $this->calculateEmployeeBreaks($employee->id, $start, $end)['total_formatted'],
                'avg_stars' => $avgStars, // <--- NUEVO KPI
                'incidents' => AttentionIncident::where('employee_id', $employee->id)->whereBetween('created_at', [$start, $end])->count(),
            ];
        });

        // --- GRÁFICA (Ajustada hasta las 19:00 / 7:00 PM) ---
        $chartTitle = $isSingleDay ? 'Flujo de Atención por Hora' : 'Flujo de Atención por Día';
        $chartData = ['labels' => [], 'data' => []];
        if ($isSingleDay) {
            $salesByHour = SalesQueue::whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->selectRaw('HOUR(completed_at) as hour, COUNT(*) as count')->groupBy('hour')->pluck('count', 'hour')->toArray();
            // Límite ajustado de 9 a 19 (7 PM)
            for ($i = 9; $i <= 19; $i++) {
                $chartData['labels'][] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $chartData['data'][] = $salesByHour[$i] ?? 0;
            }
        } else {
            $salesByDate = SalesQueue::whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->selectRaw('DATE(completed_at) as date, COUNT(*) as count')->groupBy('date')->pluck('count', 'date')->toArray();
            $currentDate = $start->copy();
            while ($currentDate->lte($end)) {
                $chartData['labels'][] = $currentDate->format('d/m');
                $chartData['data'][] = $salesByDate[$currentDate->format('Y-m-d')] ?? 0;
                $currentDate->addDay();
            }
        }

        // --- TABLAS DETALLADAS EXISTENTES ---
        $searchClient = $request->input('search_client');
        $filterType = $request->input('client_type');

        $clientsQuery = SalesQueue::with(['assignedShift.employee', 'ratings', 'customer'])
            ->whereBetween('completed_at', [$start, $end])
            ->withStatusCode('COMPLETED');

        // Filtro de búsqueda general (Turno, Nombre o Número de Cliente)
        if (!empty($searchClient)) {
            $clientsQuery->where(function ($q) use ($searchClient) {
                $q->where('turn_number', 'like', "%{$searchClient}%")
                    ->orWhere('client_name', 'like', "%{$searchClient}%")
                    ->orWhereHas('customer', function ($qCustomer) use ($searchClient) {
                        $qCustomer->where('customer_number', 'like', "%{$searchClient}%");
                    });
            });
        }

        // Filtro por Tipo de Cliente (ej. VIP)
        if (!empty($filterType)) {
            $clientsQuery->byClientType($filterType);
        }

        // Filtro por Vendedor
        if (!empty($filterSeller)) {
            $clientsQuery->whereHas('assignedShift', function ($qShift) use ($filterSeller) {
                $qShift->where('employee_id', $filterSeller);
            });
        }

        $detailedClients = $clientsQuery->with('catalogClientType')->orderBy('completed_at', 'desc')
            ->paginate(15, ['*'], 'clients_page')
            ->appends(request()->query());

        $detailedClients->getCollection()->transform(function ($client) {
            $client->formatted_wait = $this->formatSeconds($client->queued_at && $client->started_serving_at ? Carbon::parse($client->queued_at)->diffInSeconds(Carbon::parse($client->started_serving_at)) : 0);
            $client->formatted_serve = $this->formatSeconds($client->started_serving_at && $client->completed_at ? Carbon::parse($client->started_serving_at)->diffInSeconds(Carbon::parse($client->completed_at)) : 0);
            return $client;
        });

        $detailedAbandoned = SalesQueue::with('abandonmentReason')->whereBetween('queued_at', [$start, $end])->where(function ($q) {
            $q->withStatusCode('ABANDONED')->orWhere(fn ($q2) => $q2->withStatusCode('CANCELED'));
        })->orderBy('queued_at', 'desc')->paginate(15, ['*'], 'abandoned_page')->appends(request()->query());

        // --- NUEVAS PESTAÑAS: DIRECTORIOS HISTÓRICOS (CRM) ---
        $customersDirectory = null;
        $sellersDirectory = null;

        if ($activeTab === 'client_ratings') {
            // Buscador específico para el directorio de clientes
            $clientSearch = $request->input('client_search');
            $q = \App\Models\Customer::query();

            if ($clientSearch) {
                $q->where('name', 'like', "%{$clientSearch}%")
                    ->orWhere('customer_number', 'like', "%{$clientSearch}%");
            }

            // Paginamos a los clientes (porque son más de 5000)
            $customersDirectory = $q->with('catalogClientType')->paginate(15, ['*'], 'cd_page')->appends(request()->query());

            // Para cada cliente paginado, traemos TODO su historial de calificaciones (Dadas por VENDEDORES)
            $customersDirectory->getCollection()->transform(function ($customer) {
                $ratings = \App\Models\SaleRating::with('salesQueue.assignedShift.employee')
                    ->where('rater_type', 'SELLER')
                    ->whereHas('salesQueue', function ($query) use ($customer) {
                        $query->where('customer_id', $customer->id);
                    })->orderBy('created_at', 'desc')->get();

                $customer->all_time_stars = $ratings->count() > 0 ? round($ratings->avg('stars'), 1) : null;
                $customer->comments_history = $ratings;
                return $customer;
            });
        }

        if ($activeTab === 'seller_ratings') {
            // Traemos a todos los vendedores activos
            $sellersDirectory = Employee::sellers()->get()->map(function ($emp) {
                // Traemos TODO su historial de calificaciones (Dadas por CLIENTES)
                $ratings = \App\Models\SaleRating::with('salesQueue.customer')
                    ->where('rater_type', 'CLIENT')
                    ->whereHas('salesQueue.assignedShift', function ($query) use ($emp) {
                        $query->where('employee_id', $emp->id);
                    })->orderBy('created_at', 'desc')->get();

                $emp->all_time_stars = $ratings->count() > 0 ? round($ratings->avg('stars'), 1) : null;
                $emp->comments_history = $ratings;
                return $emp;
            })->sortByDesc('all_time_stars'); // Ordenamos a los mejores primero
        }

        // --- RENDIMIENTO INDIVIDUAL (Se mantiene igual) ---
        $empData = null;
        if ($selectedEmployeeId && $activeTab === 'performance') {
            $empSalesQuery = SalesQueue::with('ratings')->whereHas('assignedShift', function ($q) use ($selectedEmployeeId) {
                $q->where('employee_id', $selectedEmployeeId);
            })->whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED');

            $empServed = (clone $empSalesQuery)->count();
            $empAvgSeconds = (clone $empSalesQuery)->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');

            $empAvgStars = \App\Models\SaleRating::where('rater_type', 'CLIENT')
                ->whereIn('sales_queue_id', (clone $empSalesQuery)->select('id'))
                ->avg('stars');

            $breakAnalysis = $this->calculateEmployeeBreaks($selectedEmployeeId, $start, $end);
            $empClientsPaginated = (clone $empSalesQuery)->orderBy('completed_at', 'desc')->paginate(10, ['*'], 'emp_page')->appends(request()->query());
            $empClientsPaginated->getCollection()->transform(function ($client) {
                $client->formatted_wait = $this->formatSeconds($client->queued_at && $client->started_serving_at ? Carbon::parse($client->queued_at)->diffInSeconds(Carbon::parse($client->started_serving_at)) : 0);
                $client->formatted_serve = $this->formatSeconds($client->started_serving_at && $client->completed_at ? Carbon::parse($client->started_serving_at)->diffInSeconds(Carbon::parse($client->completed_at)) : 0);
                return $client;
            });

            $empData = [
                'employee' => Employee::find($selectedEmployeeId),
                'kpis' => [
                    'served' => $empServed,
                    'avg_time' => $this->formatSeconds($empAvgSeconds ?? 0),
                    'total_break' => $breakAnalysis['total_formatted'],
                    'total_available' => $breakAnalysis['total_available_formatted'],
                    'avg_stars' => $empAvgStars ? round($empAvgStars, 1) : 0
                ],
                'daily_breaks' => $breakAnalysis['daily_breaks'],
                'timeline' => $breakAnalysis['timeline'],
                'clients' => $empClientsPaginated
            ];
        }

        return view('admin.reports.index', [
            'period' => $period,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'activeTab' => $activeTab,
            'is_single_day' => $isSingleDay,
            'chart_title' => $chartTitle,
            'metrics' => [
                'total_served' => $totalServed,
                'total_abandoned' => $totalAbandoned,
                'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
                'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
            ],
            'employees_metrics' => $employeesMetrics,
            'chart_data' => $chartData,
            'detailedClients' => $detailedClients,
            'detailedAbandoned' => $detailedAbandoned,
            'detailedIncidents' => $detailedIncidents,
            'total_incidents' => $totalIncidents,
            'employeesList' => $employeesList,
            'clientTypes' => \App\Models\ClientType::active()->orderBy('sort_order')->get(),
            'selectedEmployeeId' => $selectedEmployeeId,
            'empData' => $empData,

            // NUEVAS VARIABLES AL VISTA
            'customersDirectory' => $customersDirectory,
            'sellersDirectory' => $sellersDirectory
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // PDF VENDEDOR — GENERACIÓN PERSONALIZADA (SÍNCRONA O ASÍNCRONA)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Punto de entrada POST: decide si genera el PDF directo o lanza un Job.
     * Criterio: periodo <= 7 días → síncrono, > 7 días → asíncrono.
     */
    public function generatePdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'sections'    => 'required|array|min:1',
            'sections.*'  => 'in:kpis,breaks,timeline,clients,ratings',
        ]);

        $empId    = (int) $request->input('employee_id');
        $start    = Carbon::parse($request->input('start_date'))->startOfDay();
        $end      = Carbon::parse($request->input('end_date'))->endOfDay();
        $sections = $request->input('sections');
        $days     = $start->diffInDays($end);

        // ── MODO SÍNCRONO (periodo corto) ─────────────────────────────────
        if ($days <= 7) {
            $data     = $this->buildEmployeePdfData($empId, $start, $end, $sections);
            $employee = Employee::find($empId);
            $pdf      = Pdf::loadView('admin.reports.pdf_employee', array_merge($data, [
                'employee'    => $employee,
                'sections'    => $sections,
                'start'       => $start,
                'end'         => $end,
                'generatedAt' => now()->format('d/m/Y H:i:s'),
            ]))->setPaper('a4', 'portrait');

            $fileName = 'Reporte_Vendedor_' . Str::slug($employee->full_name ?? $empId) . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($fileName);
        }

        // ── MODO ASÍNCRONO (periodo largo) ────────────────────────────────
        $token = Str::uuid()->toString();

        // Crear archivo de estado inicial
        Storage::put('temp_reports/' . $token . '.json', json_encode([
            'status'   => 'queued',
            'progress' => 0,
            'message'  => '',
        ]));

        GenerateEmployeePdfJob::dispatch(
            $token,
            $empId,
            $start->toDateString(),
            $end->toDateString(),
            $sections
        );

        return response()->json(['token' => $token]);
    }

    /**
     * Devuelve el estado actual del job de generación de PDF.
     */
    public function pdfStatus(string $token)
    {
        // Validar que el token tenga formato UUID para evitar path traversal
        if (! preg_match('/^[0-9a-f\-]{36}$/', $token)) {
            return response()->json(['status' => 'failed', 'message' => 'Token inválido.'], 400);
        }

        $jsonPath = 'temp_reports/' . $token . '.json';

        if (! Storage::exists($jsonPath)) {
            return response()->json(['status' => 'failed', 'message' => 'El reporte no existe o fue eliminado.'], 404);
        }

        $data = json_decode(Storage::get($jsonPath), true);
        return response()->json($data);
    }

    /**
     * Descarga el PDF generado y limpia los archivos temporales.
     */
    public function downloadPdf(string $token)
    {
        // Validar token formato UUID
        if (! preg_match('/^[0-9a-f\-]{36}$/', $token)) {
            abort(400, 'Token inválido.');
        }

        $pdfPath  = 'temp_reports/' . $token . '.pdf';
        $jsonPath = 'temp_reports/' . $token . '.json';

        if (! Storage::exists($pdfPath)) {
            abort(404, 'El archivo no está disponible. Es posible que ya haya sido descargado.');
        }

        $content  = Storage::get($pdfPath);
        $fileName = 'Reporte_Vendedor_' . now()->format('Ymd_His') . '.pdf';

        // Limpiar archivos temporales después de preparar la respuesta
        Storage::delete([$pdfPath, $jsonPath]);

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Construye el array de datos para el PDF (modo síncrono).
     */
    private function buildEmployeePdfData(int $empId, Carbon $start, Carbon $end, array $sections): array
    {
        $kpis        = [];
        $dailyBreaks = [];
        $breakTotals = [];
        $timeline    = [];
        $clientRows     = [];
        $ratingsHistory = [];

        // KPIs siempre se incluyen si está la sección 'kpis'
        if (in_array('kpis', $sections)) {
            $salesQuery = SalesQueue::whereHas('assignedShift', fn ($q) => $q->where('employee_id', $empId))
                ->whereBetween('completed_at', [$start, $end])
                ->withStatusCode('COMPLETED');

            $served     = (clone $salesQuery)->count();
            $avgSeconds = (clone $salesQuery)
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')
                ->value('avg_time') ?? 0;

            $avgStarsRaw = SaleRating::where('rater_type', 'CLIENT')
                ->whereIn('sales_queue_id', (clone $salesQuery)->select('id'))
                ->avg('stars');

            $breakInfo = $this->calculateEmployeeBreaks($empId, $start, $end);

            $kpis = [
                'served'          => $served,
                'avg_time'        => $this->formatSeconds($avgSeconds),
                'avg_stars'       => $avgStarsRaw ? round($avgStarsRaw, 1) : 0,
                'total_break'     => $breakInfo['total_formatted'],
                'total_available' => $breakInfo['total_available_formatted'],
            ];

            if (in_array('breaks', $sections)) {
                $dailyBreaks = $breakInfo['daily_breaks'];
                $breakTotals = $breakInfo['break_totals'] ?? [];
            }
            if (in_array('timeline', $sections)) {
                $timeline = array_reverse($breakInfo['timeline']);
            }
        } else {
            // Si no se piden KPIs, aún podemos necesitar pausas/timeline
            if (in_array('breaks', $sections) || in_array('timeline', $sections)) {
                $breakInfo = $this->calculateEmployeeBreaks($empId, $start, $end);
                if (in_array('breaks', $sections)) {
                    $dailyBreaks = $breakInfo['daily_breaks'];
                    $breakTotals = $breakInfo['break_totals'] ?? [];
                }
                if (in_array('timeline', $sections))  $timeline = array_reverse($breakInfo['timeline']);
            }
        }

        if (in_array('clients', $sections)) {
            $includeRatings = in_array('ratings', $sections);
            $data = SalesQueue::with($includeRatings ? ['ratings', 'customer'] : ['customer'])
                ->whereHas('assignedShift', fn ($q) => $q->where('employee_id', $empId))
                ->whereBetween('completed_at', [$start, $end])
                ->withStatusCode('COMPLETED')
                ->orderBy('completed_at', 'desc')
                ->get();

            foreach ($data as $sale) {
                $sale->loadMissing('catalogClientType');
                $row = [
                    'turn'       => $sale->turn_number,
                    'client'     => $sale->client_name,
                    'type'       => $sale->resolveClientTypeLabel(),
                    'type_code'  => $sale->resolveClientTypeCode(),
                    'use_premium_alert' => $sale->catalogClientType?->usesPremiumAlert() ?? false,
                    'no_cliente' => $sale->customer->customer_number ?? 'N/A',
                    'wait'       => $this->formatSeconds($sale->queued_at && $sale->started_serving_at
                        ? Carbon::parse($sale->queued_at)->diffInSeconds(Carbon::parse($sale->started_serving_at))
                        : 0),
                    'serve'      => $this->formatSeconds($sale->started_serving_at && $sale->completed_at
                        ? Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at))
                        : 0),
                    'status'     => str_ends_with($sale->turn_number, '-R') ? 'RE-ATENDIDO' : 'NORMAL',
                    'date'       => Carbon::parse($sale->completed_at)->format('d/m/Y H:i'),
                    'cr_stars'   => null, 'cr_tags' => [], 'cr_comment' => null,
                    'sr_stars'   => null, 'sr_tags' => [], 'sr_comment' => null,
                ];

                if ($includeRatings && $sale->ratings) {
                    $cr = $sale->ratings->where('rater_type', 'CLIENT')->first();
                    $sr = $sale->ratings->where('rater_type', 'SELLER')->first();
                    if ($cr) { $row['cr_stars'] = $cr->stars; $row['cr_tags'] = $cr->tags ?? []; $row['cr_comment'] = $cr->comments; }
                    if ($sr) { $row['sr_stars'] = $sr->stars; $row['sr_tags'] = $sr->tags ?? []; $row['sr_comment'] = $sr->comments; }
                }
                $clientRows[] = $row;
            }
        }

        if (in_array('ratings', $sections) && ! in_array('clients', $sections)) {
            // Solo historial de calificaciones sin tabla de clientes
            $ratingsHistory = SaleRating::with('salesQueue.customer')
                ->where('rater_type', 'CLIENT')
                ->whereHas('salesQueue', function ($q) use ($empId, $start, $end) {
                    $q->whereHas('assignedShift', fn ($s) => $s->where('employee_id', $empId))
                      ->whereBetween('completed_at', [$start, $end])
                      ->withStatusCode('COMPLETED');
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($r) => [
                    'stars'       => $r->stars,
                    'tags'        => $r->tags ?? [],
                    'comment'     => $r->comments,
                    'turn'        => $r->salesQueue->turn_number ?? 'N/A',
                    'client_name' => $r->salesQueue->customer->name ?? $r->salesQueue->client_name ?? 'Desconocido',
                    'date'        => $r->created_at->format('d/m/Y H:i'),
                ])->toArray();
        }

        return compact('kpis', 'dailyBreaks', 'breakTotals', 'timeline', 'clientRows', 'ratingsHistory');
    }

    // ──────────────────────────────────────────────────────────────────────
    // EXPORTACIÓN CLÁSICA (CSV / PDF antiguo) — Sin cambios
    // ──────────────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $type = $request->input('type', 'clients');
        $format = $request->input('format', 'csv');
        $start = Carbon::parse($request->input('start_date') ?? today())->startOfDay();
        $end = Carbon::parse($request->input('end_date') ?? today())->endOfDay();
        $empId = $request->input('employee_id');

        $headers = [];
        $rows = [];
        $employee = null;
        $stats = [];
        $timeline = [];
        $dailyBreaks = [];

        if ($type === 'clients') {
            // Recibimos los nuevos filtros del request
            $searchClient = $request->input('search_client');
            $filterType = $request->input('client_type');
            $filterSeller = $request->input('seller_id_filter');

            // Añadimos "No. Cliente" a las cabeceras. El PDF detectará esta cabecera y añadirá la columna automáticamente.
            $headers = ['Turno', 'No. Cliente', 'Cliente', 'Tipo', 'Vendedor', 'Tiempo Espera', 'Tiempo Atención', 'Estado', 'Fecha'];
            
            $query = SalesQueue::with(['assignedShift.employee', 'customer'])
                ->whereBetween('completed_at', [$start, $end])
                ->withStatusCode('COMPLETED');

            // Aplicamos los mismos filtros de la vista
            if (!empty($searchClient)) {
                $query->where(function ($q) use ($searchClient) {
                    $q->where('turn_number', 'like', "%{$searchClient}%")
                      ->orWhere('client_name', 'like', "%{$searchClient}%")
                      ->orWhereHas('customer', function ($qCustomer) use ($searchClient) {
                          $qCustomer->where('customer_number', 'like', "%{$searchClient}%");
                      });
                });
            }

            if (!empty($filterType)) {
                $query->byClientType($filterType);
            }

            if (!empty($filterSeller)) {
                $query->whereHas('assignedShift', function ($qShift) use ($filterSeller) {
                    $qShift->where('employee_id', $filterSeller);
                });
            }

            $data = $query->orderBy('completed_at', 'desc')->get();

            foreach($data as $d) {
                $wait = $this->formatSeconds(Carbon::parse($d->queued_at)->diffInSeconds(Carbon::parse($d->started_serving_at)));
                $serve = $this->formatSeconds(Carbon::parse($d->started_serving_at)->diffInSeconds(Carbon::parse($d->completed_at)));
                
                // Mapeamos los datos respetando el orden de las cabeceras
                $rows[] = [
                    $d->turn_number, 
                    $d->customer->customer_number ?? 'N/A', 
                    $d->client_name, 
                    $d->resolveClientTypeName(), 
                    $d->assignedShift->employee->full_name ?? 'N/A', 
                    $wait, 
                    $serve, 
                    str_ends_with($d->turn_number, '-R') ? 'RE-ATENDIDO' : 'NORMAL', 
                    Carbon::parse($d->completed_at)->format('d/m/Y H:i:s')
                ];
            }
        } elseif ($type === 'abandoned') {
            $headers = ['Turno', 'Cliente', 'Tipo', 'Fecha/Hora', 'Motivo de Abandono'];
            $data = SalesQueue::with('abandonmentReason')->whereBetween('queued_at', [$start, $end])->where(function ($q) {
                $q->withStatusCode('ABANDONED')->orWhere(fn ($q2) => $q2->withStatusCode('CANCELED'));
            })->orderBy('queued_at', 'desc')->get();
            foreach ($data as $d) {
                $motivo = !empty($d->custom_abandonment_reason) ? $d->custom_abandonment_reason : ($d->abandonment_reason_id ? $d->abandonmentReason->reason : 'Inactividad');
                $rows[] = [$d->turn_number, $d->client_name, $d->resolveClientTypeName(), Carbon::parse($d->queued_at)->format('d/m/Y H:i:s'), $motivo];
            }
        } elseif ($type === 'incidents') {
            $headers = ['Fecha / Hora', 'Turno / Cliente', 'Vendedor', 'Razón / Detalle', 'Prórrogas', 'Tiempo Atendido'];
            $incidentsQuery = AttentionIncident::with(['employee', 'salesQueue', 'customer'])->whereBetween('created_at', [$start, $end]);
            
            $filterSeller = $request->input('seller_id_filter');
            if (!empty($filterSeller)) {
                $incidentsQuery->where('employee_id', $filterSeller);
            }
            $data = $incidentsQuery->orderBy('created_at', 'desc')->get();

            foreach ($data as $incident) {
                $queue = $incident->salesQueue;
                $prorrogas = $queue ? ($queue->extension_count ?? 0) : 0;
                $tiempoAtendido = 'N/A';
                if ($queue && $queue->started_serving_at) {
                    $endQueue = $queue->completed_at ? Carbon::parse($queue->completed_at) : Carbon::parse($incident->created_at);
                    $diff = Carbon::parse($queue->started_serving_at)->diffInSeconds($endQueue);
                    $tiempoAtendido = gmdate($diff >= 3600 ? "H:i:s" : "i:s", $diff);
                }

                $rows[] = [
                    optional($incident->created_at)->format('d/m/Y H:i:s'),
                    ($incident->turn_number ?? 'N/A') . ' - ' . ($incident->client_name ?? optional($incident->customer)->full_name ?? 'N/A'),
                    optional($incident->employee)->full_name ?? 'N/A',
                    $incident->reason . ' - ' . ($incident->details ?? '-'),
                    $prorrogas,
                    $tiempoAtendido
                ];
            }
        } elseif ($type === 'employee' && $empId) {
            $employee = Employee::find($empId);
            $headers = ['Turno', 'Cliente', 'Tipo Cliente', 'Tiempo Atención', 'Estado', 'Fecha/Hora'];
            $data = SalesQueue::whereHas('assignedShift', function ($q) use ($empId) {
                $q->where('employee_id', $empId);
            })->whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->orderBy('completed_at', 'desc')->get();

            $servedCount = $data->count();
            $avgEmpServiceSeconds = $servedCount > 0 ? ($data->reduce(function ($carry, $sale) {
                return $carry + Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at));
            }, 0) / $servedCount) : 0;
            $breakAnalysis = $this->calculateEmployeeBreaks($empId, $start, $end);

            $stats = ['served' => $servedCount, 'avg_time' => $this->formatSeconds($avgEmpServiceSeconds), 'break_time' => $breakAnalysis['total_formatted']];
            $timeline = array_reverse($breakAnalysis['timeline']);
            $dailyBreaks = $breakAnalysis['daily_breaks']; // PASAMOS EL DESGLOSE AL PDF

            foreach ($data as $d) {
                $serve = $this->formatSeconds(Carbon::parse($d->started_serving_at)->diffInSeconds(Carbon::parse($d->completed_at)));
                $rows[] = [$d->turn_number, $d->client_name, $d->resolveClientTypeName(), $serve, str_ends_with($d->turn_number, '-R') ? 'RE-ATENDIDO' : 'NORMAL', Carbon::parse($d->completed_at)->format('d/m/Y H:i:s')];
            }
        }

        $fileName = 'Reporte_TERA_' . ucfirst($type) . '_' . now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.pdf', compact('type', 'start', 'end', 'headers', 'rows', 'employee', 'stats', 'timeline', 'dailyBreaks'));
            return $pdf->download($fileName . '.pdf');
        }

        $callback = function () use ($headers, $rows) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            fputcsv($file, $headers);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName.csv"]);
    }

    // --- CÁLCULO DE PAUSAS Y TIEMPO DISPONIBLE (TRADUCIDO Y AGRUPADO POR DÍA) ---
    private function calculateEmployeeBreaks($employeeId, $start, $end)
    {
        $shifts = DailyShift::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->with(['statusLogs' => function ($q) {
                $q->with(['catalogBreakReason', 'approvedBy'])->orderBy('changed_at', 'asc');
            }])
            ->get();

        $totalBreakSeconds = 0;
        $totalAvailableSeconds = 0;
        $dailySeconds = [];
        $timeline = [];

        $servingSecondsByShift = [];
        $shiftIds = $shifts->pluck('id')->toArray();
        if (! empty($shiftIds)) {
            $servingSecondsByShift = \App\Models\SalesQueue::whereIn('assigned_shift_id', $shiftIds)
                ->withStatusCode('COMPLETED')
                ->whereNotNull('started_serving_at')
                ->whereNotNull('completed_at')
                ->groupBy('assigned_shift_id')
                ->selectRaw('assigned_shift_id, SUM(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as total_seconds')
                ->pluck('total_seconds', 'assigned_shift_id')
                ->toArray();
        }

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
                        $reasonTrans = $this->breakReasonLabels()[$log->resolveReasonCode()] ?? $log->resolveReasonLabel();
                        $statusLabel .= ' (' . $reasonTrans . ')';
                    }
                    if ($log->approvedBy) {
                        $statusLabel .= ' [Aprob: ' . strtok($log->approvedBy->name, " ") . ']';
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
                    $currentReason = $log->resolveReasonCode() ?? 'GENERAL';
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

            // --- 2.5 Lógica para Eventos de Cola (Prórrogas y Ventas Terminadas) ---
            $queueLogs = \App\Models\QueueActionLog::whereHas('salesQueue', function($q) use ($shift) {
                $q->where('assigned_shift_id', $shift->id);
            })->with(['user', 'salesQueue'])->get();

            foreach ($queueLogs as $qLog) {
                $label = '';
                $turn = $qLog->salesQueue->turn_number;
                if ($qLog->action_type === 'EXTENSION') {
                    $label = 'Prórroga solicitada por ' . ($qLog->user->name ?? 'Usuario') . ' [Turno: ' . $turn . ']';
                } elseif ($qLog->action_type === 'FINISHED') {
                    $label = 'Venta terminada por ' . ($qLog->user->name ?? 'Usuario') . ' [Turno: ' . $turn . ']';
                }

                $timeline[] = [
                    'status' => $label,
                    'time' => \Carbon\Carbon::parse($qLog->created_at)->format('H:i:s'),
                    'date' => \Carbon\Carbon::parse($qLog->created_at)->format('d/m/Y'),
                    'color' => 'text-blue-400'
                ];
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
            $servingSeconds = $servingSecondsByShift[$shift->id] ?? 0;

            $dailySeconds[$dateStr]['AVAILABLE'] -= $servingSeconds;
            if ($dailySeconds[$dateStr]['AVAILABLE'] < 0) $dailySeconds[$dateStr]['AVAILABLE'] = 0;

            $totalAvailableSeconds += $dailySeconds[$dateStr]['AVAILABLE'];
        }

        // --- Ordenar la línea de tiempo de forma cronológica ---
        usort($timeline, function($a, $b) {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $a['date'] . ' ' . $a['time']) <=> \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $b['date'] . ' ' . $b['time']);
        });

        // Formateamos las matrices para enviarlas a Blade
        $formattedDailyBreaks = [];
        foreach ($dailySeconds as $date => $reasonsArray) {
            $formattedDailyBreaks[$date] = [];
            foreach ($reasonsArray as $reason => $secs) {
                if ($reason === 'AVAILABLE') {
                    // Colocamos "Tiempo Disponible" al inicio del arreglo visual
                    $formattedDailyBreaks[$date] = ['Tiempo Disponible' => $this->formatSeconds($secs)] + $formattedDailyBreaks[$date];
                } else {
                    $reasonName = $this->breakReasonLabels()[$reason] ?? $reason;
                    $formattedDailyBreaks[$date][$reasonName] = $this->formatSeconds($secs);
                }
            }
        }

        // --- TOTALES POR TIPO DE PAUSA (suma de todos los días) ---
        $totalsByReason = ['LUNCH' => 0, 'BATHROOM' => 0, 'ERRAND' => 0, 'PACKAGING' => 0, 'GENERAL' => 0, 'AVAILABLE' => 0];
        foreach ($dailySeconds as $dayData) {
            foreach ($dayData as $reason => $secs) {
                if (isset($totalsByReason[$reason])) {
                    $totalsByReason[$reason] += $secs;
                }
            }
        }
        $formattedBreakTotals = ['Tiempo Disponible' => $this->formatSeconds($totalsByReason['AVAILABLE'])];
        foreach (['LUNCH', 'BATHROOM', 'ERRAND', 'PACKAGING', 'GENERAL'] as $r) {
            $formattedBreakTotals[$this->breakReasonLabels()[$r] ?? $r] = $this->formatSeconds($totalsByReason[$r]);
        }

        return [
            'total_formatted'           => $this->formatSeconds($totalBreakSeconds),
            'total_available_formatted' => $this->formatSeconds($totalAvailableSeconds),
            'daily_breaks'              => $formattedDailyBreaks,
            'break_totals'              => $formattedBreakTotals,
            'timeline'                  => $timeline
        ];
    }

    public function realTimeData()
    {
        $sellers = DailyShift::with([
            'employee',
            'servedCustomers' => function ($q) {
                $q->serving();
            }
        ])
            ->with(['catalogBreakReason'])
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
                } elseif ($shift->current_status === 'BREAK' && $shift->last_status_change_at) {
                    $state = 'BREAK';
                    $stateStartedAt = Carbon::parse($shift->last_status_change_at)->timestamp * 1000;
                    $breakReason = $shift->resolveBreakReasonLabel();
                } elseif ($shift->current_status === 'ONLINE' && $shift->last_status_change_at) {
                    $state = 'ONLINE';

                    // Buscar a qué hora terminó su última venta de hoy
                    $lastSale = \App\Models\SalesQueue::where('assigned_shift_id', $shift->id)
                        ->withStatusCode('COMPLETED')
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

    private function breakReasonLabels(): array
    {
        return BreakReason::labelsByCode();
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
