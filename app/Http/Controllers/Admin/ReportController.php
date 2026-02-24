<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Importamos los modelos
use App\Models\SalesQueue;
use App\Models\DailyShift;
use App\Models\Pickup;
use App\Models\PickupEdit;
use App\Models\Employee;
use App\Models\ShiftStatusLog; 

use Rap2hpoutre\FastExcel\FastExcel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'today');
        $selectedEmployeeId = $request->query('employee_id'); // <-- NUEVO: Capturamos si se seleccionó un empleado
        $activeTab = $request->query('tab', 'dashboard');     // <-- NUEVO: Capturamos la pestaña activa

        $querySales = SalesQueue::query();
        $queryPickups = Pickup::query();
        $queryShifts = DailyShift::query(); 

        if ($period === 'today') {
            $querySales->whereDate('queued_at', Carbon::today());
            $queryPickups->whereDate('created_at', Carbon::today());
            $queryShifts->whereDate('work_date', Carbon::today());
        } elseif ($period === '7days') {
            $querySales->where('queued_at', '>=', Carbon::today()->subDays(7));
            $queryPickups->where('created_at', '>=', Carbon::today()->subDays(7));
            $queryShifts->where('work_date', '>=', Carbon::today()->subDays(7));
        } elseif ($period === 'month') {
            $querySales->whereMonth('queued_at', Carbon::now()->month)
                       ->whereYear('queued_at', Carbon::now()->year);
            $queryPickups->whereMonth('created_at', Carbon::now()->month)
                         ->whereYear('created_at', Carbon::now()->year);
            $queryShifts->whereMonth('work_date', Carbon::now()->month)
                        ->whereYear('work_date', Carbon::now()->year);
        }

        // --- 1. MÉTRICAS CORE (DASHBOARD GENERAL) ---
        $totalAtendidos = (clone $querySales)->where('status', 'COMPLETED')->count();
        $totalAbandonos = (clone $querySales)->where('status', 'ABANDONED')->count();
        $peticionesTiempo = (clone $querySales)->sum('extension_count');
        
        $tiempos = (clone $querySales)->select(
            DB::raw('AVG(TIMESTAMPDIFF(MINUTE, queued_at, started_serving_at)) as avg_wait'),
            DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_serving_at, completed_at)) as avg_service')
        )->where('status', 'COMPLETED')->first();

        $avgWaitTime = round($tiempos->avg_wait ?? 0); 
        $avgServiceTime = round($tiempos->avg_service ?? 0); 

        $topSellers = (clone $querySales)->select('employees.full_name', DB::raw('COUNT(sales_queue.id) as total_sales'))
            ->join('daily_shifts', 'sales_queue.assigned_shift_id', '=', 'daily_shifts.id')
            ->join('employees', 'daily_shifts.employee_id', '=', 'employees.id')
            ->where('sales_queue.status', 'COMPLETED')
            ->groupBy('employees.id', 'employees.full_name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        $serviceTypes = (clone $querySales)->select('service_type', DB::raw('COUNT(*) as total'))
            ->groupBy('service_type')
            ->get();

        $pickupsByDept = (clone $queryPickups)->select('department', DB::raw('COUNT(*) as total'))
            ->groupBy('department')
            ->get();

        $breakReasons = (clone $queryShifts)->select('break_reason', DB::raw('COUNT(*) as total'))
            ->whereNotNull('break_reason')
            ->groupBy('break_reason')
            ->get();

        $thirdPartyPickups = (clone $queryPickups)->select('is_third_party', DB::raw('COUNT(*) as total'))
            ->groupBy('is_third_party')
            ->get();

        $warehouseTime = (clone $queryPickups)->select(
            DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, delivered_at)) as avg_warehouse_time')
        )->whereNotNull('delivered_at')->first();
        
        $avgWarehouseTime = round($warehouseTime->avg_warehouse_time ?? 0);

        // --- 2. NUEVO: MÉTRICAS INDIVIDUALES (PESTAÑA RENDIMIENTO) ---
        $employees = Employee::sellers()->where('is_active', true)->get();
        
        $empPerformanceData = [];
        $empBreaksData = [];
        $empKpis = ['served' => 0, 'abandoned' => 0, 'extensions' => 0, 'avg_time' => 0];

        if ($selectedEmployeeId) {
            // Historial de tiempos para la gráfica de líneas curvas
            $empPerformanceData = (clone $querySales)
                ->join('daily_shifts', 'sales_queue.assigned_shift_id', '=', 'daily_shifts.id')
                ->where('daily_shifts.employee_id', $selectedEmployeeId)
                ->where('sales_queue.status', 'COMPLETED')
                ->select(
                    'sales_queue.queued_at',
                    DB::raw('TIMESTAMPDIFF(MINUTE, queued_at, started_serving_at) as wait_time'),
                    DB::raw('TIMESTAMPDIFF(MINUTE, started_serving_at, completed_at) as service_time')
                )
                ->orderBy('sales_queue.queued_at', 'asc')
                ->get();

            // Historial de Breaks del empleado
            $empBreaksData = (clone $queryShifts)
                ->where('employee_id', $selectedEmployeeId)
                ->whereNotNull('break_reason')
                ->select('break_reason', DB::raw('COUNT(*) as total'))
                ->groupBy('break_reason')
                ->get();

            // KPIs individuales
            $baseEmpQuery = (clone $querySales)->join('daily_shifts', 'sales_queue.assigned_shift_id', '=', 'daily_shifts.id')->where('daily_shifts.employee_id', $selectedEmployeeId);
            $empKpis['served'] = (clone $baseEmpQuery)->where('sales_queue.status', 'COMPLETED')->count();
            $empKpis['abandoned'] = (clone $baseEmpQuery)->where('sales_queue.status', 'ABANDONED')->count();
            $empKpis['extensions'] = (clone $baseEmpQuery)->sum('extension_count');
            
            $empAvgTime = (clone $baseEmpQuery)->where('sales_queue.status', 'COMPLETED')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_serving_at, completed_at)) as avg_s'))->first();
            $empKpis['avg_time'] = round($empAvgTime->avg_s ?? 0);
        }

        // --- 3. AUDITORÍA ---
        $audits = PickupEdit::select('pickup_edits.*', 'users.name as user_name', 'users.role as user_role')
            ->join('users', 'pickup_edits.user_id', '=', 'users.id')
            ->orderBy('pickup_edits.created_at', 'desc')
            ->paginate(20);

        return view('admin.reports.index', compact(
            'period', 'activeTab', 'selectedEmployeeId',
            'totalAtendidos', 'totalAbandonos', 'peticionesTiempo', 'avgWaitTime', 'avgServiceTime',
            'topSellers', 'serviceTypes', 'pickupsByDept', 'breakReasons', 'thirdPartyPickups', 'avgWarehouseTime',   
            'employees', 'audits',
            'empPerformanceData', 'empBreaksData', 'empKpis' // <-- Enviamos datos individuales
        ));
    }

    public function export(Request $request)
    {
        $query = SalesQueue::query()
            ->leftJoin('daily_shifts', 'sales_queue.assigned_shift_id', '=', 'daily_shifts.id')
            ->leftJoin('employees', 'daily_shifts.employee_id', '=', 'employees.id')
            ->select(
                'sales_queue.client_name',
                'sales_queue.service_type',
                'sales_queue.status',
                'sales_queue.queued_at',
                'sales_queue.started_serving_at',
                'sales_queue.completed_at',
                'sales_queue.extension_count',
                'employees.full_name as seller_name'
            )
            ->orderBy('sales_queue.queued_at', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sales_queue.queued_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('employee_id')) {
            $query->where('employees.id', $request->employee_id);
        }

        $timestamp = Carbon::now()->format('Y_m_d_His');
        
        $generator = function () use ($query) {
            foreach ($query->cursor() as $row) {
                yield $row;
            }
        };

        return (new FastExcel($generator()))->download("reporte_atencion_{$timestamp}.xlsx", function ($row) {
            $queued = $row->queued_at ? Carbon::parse($row->queued_at) : null;
            $started = $row->started_serving_at ? Carbon::parse($row->started_serving_at) : null;
            $completed = $row->completed_at ? Carbon::parse($row->completed_at) : null;

            $waitSeconds = ($queued && $started) ? $queued->diffInSeconds($started) : 0;
            $serviceSeconds = ($started && $completed) ? $started->diffInSeconds($completed) : 0;
            
            return [
                'Cliente' => $row->client_name,
                'Servicio' => $row->service_type === 'SALES' ? 'Ventas' : 'Cajas',
                'Atendido Por' => $row->seller_name ?? 'Sin asignar',
                'Estatus' => $row->status,
                'Llegada' => $queued ? $queued->format('d/m/Y H:i:s') : 'N/A',
                'Inicio Atención' => $started ? $started->format('d/m/Y H:i:s') : 'N/A',
                'Fin Atención' => $completed ? $completed->format('d/m/Y H:i:s') : 'N/A',
                'Tiempo Espera' => gmdate('H:i:s', $waitSeconds),
                'Tiempo Atención' => gmdate('H:i:s', $serviceSeconds),
                'Extensiones' => $row->extension_count,
            ];
        });
    }
}