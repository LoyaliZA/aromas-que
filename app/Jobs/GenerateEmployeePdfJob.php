<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Employee;
use App\Models\SalesQueue;
use App\Models\DailyShift;
use App\Models\SaleRating;
use Carbon\Carbon;

class GenerateEmployeePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de segundos que el job puede ejecutarse antes de fallar.
     */
    public int $timeout = 300;

    /**
     * Número de intentos si el job falla.
     */
    public int $tries = 1;

    private array $reasonsDict = [
        'LUNCH'     => 'Comida',
        'BATHROOM'  => 'Baño',
        'ERRAND'    => 'Mandado',
        'PACKAGING' => 'Empaque',
        'GENERAL'   => 'General / Otros',
    ];

    private array $statusDict = [
        'ONLINE'  => 'DISPONIBLE',
        'BREAK'   => 'PAUSA',
        'OFFLINE' => 'INACTIVO',
        'SERVING' => 'ATENDIENDO',
    ];

    public function __construct(
        public readonly string $token,
        public readonly int    $employeeId,
        public readonly string $start,
        public readonly string $end,
        public readonly array  $sections,
    ) {}

    public function handle(): void
    {
        $this->updateStatus('processing', 5);

        try {
            $startDate = Carbon::parse($this->start)->startOfDay();
            $endDate   = Carbon::parse($this->end)->endOfDay();

            $employee = Employee::find($this->employeeId);
            if (! $employee) {
                $this->updateStatus('failed', 0, 'Vendedor no encontrado.');
                return;
            }

            // ─── KPIs ─────────────────────────────────────────────────────────
            $this->updateStatus('processing', 15);

            $kpis = $this->buildKpis($employee->id, $startDate, $endDate);

            // ─── Pausas por día ───────────────────────────────────────────────
            $this->updateStatus('processing', 35);

            $breakAnalysis = in_array('breaks', $this->sections)
                ? $this->calculateBreaks($employee->id, $startDate, $endDate)
                : ['daily_breaks' => [], 'break_totals' => [], 'timeline' => [], 'total_formatted' => '00:00', 'total_available_formatted' => '00:00'];

            // ─── Historial de clientes ────────────────────────────────────────
            $this->updateStatus('processing', 55);

            $clientRows = [];
            if (in_array('clients', $this->sections)) {
                $clientRows = $this->buildClientRows($employee->id, $startDate, $endDate);
            }

            // ─── Calificaciones de clientes ───────────────────────────────────
            $this->updateStatus('processing', 75);

            $ratingsHistory = [];
            if (in_array('ratings', $this->sections)) {
                $ratingsHistory = $this->buildRatingsHistory($employee->id, $startDate, $endDate);
            }

            // ─── Generar PDF ──────────────────────────────────────────────────
            $this->updateStatus('processing', 88);

            $pdf = Pdf::loadView('admin.reports.pdf_employee', [
                'employee'        => $employee,
                'sections'        => $this->sections,
                'kpis'            => $kpis,
                'dailyBreaks'     => $breakAnalysis['daily_breaks'],
                'breakTotals'     => $breakAnalysis['break_totals'] ?? [],
                'timeline'        => $breakAnalysis['timeline'],
                'clientRows'      => $clientRows,
                'ratingsHistory'  => $ratingsHistory,
                'start'           => $startDate,
                'end'             => $endDate,
                'generatedAt'     => now()->format('d/m/Y H:i:s'),
            ]);

            $pdf->setPaper('a4', 'portrait');

            // Guardar en storage temporal
            $path = 'temp_reports/' . $this->token . '.pdf';
            Storage::put($path, $pdf->output());

            $this->updateStatus('done', 100);

        } catch (\Throwable $e) {
            $this->updateStatus('failed', 0, $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    private function buildKpis(int $empId, Carbon $start, Carbon $end): array
    {
        $salesQuery = SalesQueue::whereHas('assignedShift', fn ($q) => $q->where('employee_id', $empId))
            ->whereBetween('completed_at', [$start, $end])
            ->where('status', 'COMPLETED');

        $served = (clone $salesQuery)->count();

        $avgSeconds = (clone $salesQuery)
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')
            ->value('avg_time') ?? 0;

        $avgStarsRaw = SaleRating::where('rater_type', 'CLIENT')
            ->whereIn('sales_queue_id', (clone $salesQuery)->select('id'))
            ->avg('stars');

        $breakAnalysisForKpis = $this->calculateBreaks($empId, $start, $end);

        return [
            'served'           => $served,
            'avg_time'         => $this->formatSeconds($avgSeconds),
            'avg_stars'        => $avgStarsRaw ? round($avgStarsRaw, 1) : 0,
            'total_break'      => $breakAnalysisForKpis['total_formatted'],
            'total_available'  => $breakAnalysisForKpis['total_available_formatted'],
        ];
    }

    private function buildClientRows(int $empId, Carbon $start, Carbon $end): array
    {
        $rows = [];
        $includeRatings = in_array('ratings', $this->sections);

        // Procesamos en bloques de 200 para no saturar memoria
        SalesQueue::with($includeRatings ? ['ratings', 'customer'] : ['customer'])
            ->whereHas('assignedShift', fn ($q) => $q->where('employee_id', $empId))
            ->whereBetween('completed_at', [$start, $end])
            ->where('status', 'COMPLETED')
            ->orderBy('completed_at', 'desc')
            ->chunk(200, function ($chunk) use (&$rows, $includeRatings) {
                foreach ($chunk as $sale) {
                    $wait  = $this->formatSeconds(
                        $sale->queued_at && $sale->started_serving_at
                            ? Carbon::parse($sale->queued_at)->diffInSeconds(Carbon::parse($sale->started_serving_at))
                            : 0
                    );
                    $serve = $this->formatSeconds(
                        $sale->started_serving_at && $sale->completed_at
                            ? Carbon::parse($sale->started_serving_at)->diffInSeconds(Carbon::parse($sale->completed_at))
                            : 0
                    );

                    $row = [
                        'turn'       => $sale->turn_number,
                        'client'     => $sale->client_name,
                        'type'       => $sale->client_type,
                        'no_cliente' => $sale->customer->customer_number ?? 'N/A',
                        'wait'       => $wait,
                        'serve'      => $serve,
                        'status'     => str_ends_with($sale->turn_number, '-R') ? 'RE-ATENDIDO' : 'NORMAL',
                        'date'       => Carbon::parse($sale->completed_at)->format('d/m/Y H:i'),
                        'cr_stars'   => null,
                        'cr_tags'    => [],
                        'cr_comment' => null,
                        'sr_stars'   => null,
                        'sr_tags'    => [],
                        'sr_comment' => null,
                    ];

                    if ($includeRatings && $sale->ratings) {
                        $cr = $sale->ratings->where('rater_type', 'CLIENT')->first();
                        $sr = $sale->ratings->where('rater_type', 'SELLER')->first();
                        if ($cr) {
                            $row['cr_stars']   = $cr->stars;
                            $row['cr_tags']    = $cr->tags ?? [];
                            $row['cr_comment'] = $cr->comments;
                        }
                        if ($sr) {
                            $row['sr_stars']   = $sr->stars;
                            $row['sr_tags']    = $sr->tags ?? [];
                            $row['sr_comment'] = $sr->comments;
                        }
                    }

                    $rows[] = $row;
                }
            });

        return $rows;
    }

    private function buildRatingsHistory(int $empId, Carbon $start, Carbon $end): array
    {
        return SaleRating::with('salesQueue.customer')
            ->where('rater_type', 'CLIENT')
            ->whereHas('salesQueue', function ($q) use ($empId, $start, $end) {
                $q->whereHas('assignedShift', fn ($s) => $s->where('employee_id', $empId))
                  ->whereBetween('completed_at', [$start, $end])
                  ->where('status', 'COMPLETED');
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
            ])
            ->toArray();
    }

    private function calculateBreaks(int $employeeId, Carbon $start, Carbon $end): array
    {
        $shifts = DailyShift::where('employee_id', $employeeId)
            ->whereBetween('work_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->with(['statusLogs' => fn ($q) => $q->orderBy('changed_at', 'asc')])
            ->get();

        $totalBreakSeconds    = 0;
        $totalAvailableSeconds = 0;
        $dailySeconds         = [];
        $timeline             = [];

        foreach ($shifts as $shift) {
            $breakStart    = null;
            $onlineStart   = null;
            $currentReason = null;
            $dateStr       = Carbon::parse($shift->work_date)->format('Y-m-d');

            if (! isset($dailySeconds[$dateStr])) {
                $dailySeconds[$dateStr] = [
                    'LUNCH' => 0, 'BATHROOM' => 0, 'ERRAND' => 0,
                    'PACKAGING' => 0, 'GENERAL' => 0, 'AVAILABLE' => 0,
                ];
            }

            foreach ($shift->statusLogs as $log) {
                $transStatus = $this->statusDict[$log->new_status] ?? $log->new_status;

                if ($log->new_status === 'BREAK' || $log->previous_status === 'BREAK') {
                    $statusLabel = $transStatus;
                    if ($log->new_status === 'BREAK') {
                        $reasonTrans  = $this->reasonsDict[$log->reason] ?? ($log->reason ?? 'General');
                        $statusLabel .= ' (' . $reasonTrans . ')';
                    }
                    $timeline[] = [
                        'status' => $statusLabel,
                        'time'   => Carbon::parse($log->changed_at)->format('H:i:s'),
                        'date'   => Carbon::parse($log->changed_at)->format('d/m/Y'),
                        'color'  => $log->new_status === 'BREAK' ? 'text-yellow-500' : 'text-green-400',
                    ];
                }

                $logTime = Carbon::parse($log->changed_at);

                if ($log->new_status === 'BREAK') {
                    $breakStart    = $logTime;
                    $currentReason = $log->reason ?? 'GENERAL';
                    if ($onlineStart) {
                        $dailySeconds[$dateStr]['AVAILABLE'] += $onlineStart->diffInSeconds($logTime);
                        $onlineStart = null;
                    }
                } elseif ($log->new_status === 'ONLINE') {
                    $onlineStart = $logTime;
                    if ($breakStart && $log->previous_status === 'BREAK') {
                        $duration                               = $breakStart->diffInSeconds($logTime);
                        $totalBreakSeconds                     += $duration;
                        $dailySeconds[$dateStr][$currentReason] += $duration;
                        $breakStart = null;
                    }
                } elseif ($log->new_status === 'OFFLINE') {
                    if ($onlineStart) {
                        $dailySeconds[$dateStr]['AVAILABLE'] += $onlineStart->diffInSeconds($logTime);
                        $onlineStart = null;
                    }
                    if ($breakStart && $log->previous_status === 'BREAK') {
                        $duration                               = $breakStart->diffInSeconds($logTime);
                        $totalBreakSeconds                     += $duration;
                        $dailySeconds[$dateStr][$currentReason] += $duration;
                        $breakStart = null;
                    }
                }
            }

            if ($shift->work_date == today()->format('Y-m-d')) {
                if ($onlineStart && $shift->current_status === 'ONLINE') {
                    $dailySeconds[$dateStr]['AVAILABLE'] += $onlineStart->diffInSeconds(now());
                }
                if ($breakStart && $shift->current_status === 'BREAK') {
                    $duration                               = $breakStart->diffInSeconds(now());
                    $totalBreakSeconds                     += $duration;
                    $dailySeconds[$dateStr][$currentReason] += $duration;
                }
            }

            $servingSeconds = SalesQueue::where('assigned_shift_id', $shift->id)
                ->where('status', 'COMPLETED')
                ->whereNotNull('started_serving_at')
                ->whereNotNull('completed_at')
                ->get()
                ->reduce(fn ($carry, $s) =>
                    $carry + Carbon::parse($s->started_serving_at)->diffInSeconds(Carbon::parse($s->completed_at)),
                    0
                );

            $dailySeconds[$dateStr]['AVAILABLE'] -= $servingSeconds;
            if ($dailySeconds[$dateStr]['AVAILABLE'] < 0) {
                $dailySeconds[$dateStr]['AVAILABLE'] = 0;
            }
            $totalAvailableSeconds += $dailySeconds[$dateStr]['AVAILABLE'];
        }

        $formattedDailyBreaks = [];
        foreach ($dailySeconds as $date => $reasonsArray) {
            $formattedDailyBreaks[$date] = [];
            foreach ($reasonsArray as $reason => $secs) {
                if ($reason === 'AVAILABLE') {
                    $formattedDailyBreaks[$date] = ['Tiempo Disponible' => $this->formatSeconds($secs)] + $formattedDailyBreaks[$date];
                } else {
                    $reasonName = $this->reasonsDict[$reason] ?? $reason;
                    $formattedDailyBreaks[$date][$reasonName] = $this->formatSeconds($secs);
                }
            }
        }

        // --- TOTALES POR TIPO DE PAUSA ---
        $totalsByReason = ['LUNCH' => 0, 'BATHROOM' => 0, 'ERRAND' => 0, 'PACKAGING' => 0, 'GENERAL' => 0, 'AVAILABLE' => 0];
        foreach ($dailySeconds as $dayData) {
            foreach ($dayData as $reason => $secs) {
                if (isset($totalsByReason[$reason])) $totalsByReason[$reason] += $secs;
            }
        }
        $formattedBreakTotals = ['Tiempo Disponible' => $this->formatSeconds($totalsByReason['AVAILABLE'])];
        foreach (['LUNCH', 'BATHROOM', 'ERRAND', 'PACKAGING', 'GENERAL'] as $r) {
            $formattedBreakTotals[$this->reasonsDict[$r] ?? $r] = $this->formatSeconds($totalsByReason[$r]);
        }

        return [
            'total_formatted'           => $this->formatSeconds($totalBreakSeconds),
            'total_available_formatted' => $this->formatSeconds($totalAvailableSeconds),
            'daily_breaks'              => $formattedDailyBreaks,
            'break_totals'              => $formattedBreakTotals,
            'timeline'                  => array_reverse($timeline),
        ];
    }

    private function formatSeconds(float|int $totalSeconds): string
    {
        if (! $totalSeconds || $totalSeconds <= 0) return '00:00';
        $hours   = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = round($totalSeconds % 60);
        $result  = '';
        if ($hours > 0)  $result .= $hours . 'h ';
        if ($minutes > 0 || $hours > 0) $result .= $minutes . 'm ';
        if ($seconds > 0 || ($hours == 0 && $minutes == 0)) $result .= $seconds . 's';
        return trim($result);
    }

    private function updateStatus(string $status, int $progress, string $message = ''): void
    {
        $path = 'temp_reports/' . $this->token . '.json';
        Storage::put($path, json_encode([
            'status'   => $status,
            'progress' => $progress,
            'message'  => $message,
        ]));
    }
}
