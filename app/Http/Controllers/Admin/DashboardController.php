<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyShift;
use App\Models\QueueStatus;
use App\Models\SalesQueue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->count();
        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])
            ->where(function ($q) {
                $q->withStatusCode('ABANDONED')->orWhere(function ($q2) {
                    $q2->withStatusCode('CANCELED');
                });
            })->count();
        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])->whereNotNull('started_serving_at')->whereNotNull('completed_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])->whereNotNull('queued_at')->whereNotNull('started_serving_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')->value('avg_time');

        $currentQueue = SalesQueue::waiting()->get();

        return view('admin.dashboard', [
            'metrics' => [
                'total_served' => $totalServed,
                'total_abandoned' => $totalAbandoned,
                'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
                'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
            ],
            'currentQueue' => $currentQueue,
        ]);
    }

    public function realTimeDashboard()
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();

        $totalServed = SalesQueue::whereBetween('completed_at', [$start, $end])->withStatusCode('COMPLETED')->count();
        $totalAbandoned = SalesQueue::whereBetween('queued_at', [$start, $end])
            ->where(function ($q) {
                $q->withStatusCode('ABANDONED')->orWhere(function ($q2) {
                    $q2->withStatusCode('CANCELED');
                });
            })->count();
        $avgServiceSeconds = SalesQueue::whereBetween('completed_at', [$start, $end])->whereNotNull('started_serving_at')->whereNotNull('completed_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_serving_at, completed_at)) as avg_time')->value('avg_time');
        $avgWaitSeconds = SalesQueue::whereBetween('started_serving_at', [$start, $end])->whereNotNull('queued_at')->whereNotNull('started_serving_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queued_at, started_serving_at)) as avg_time')->value('avg_time');

        $metrics = [
            'total_served' => $totalServed,
            'total_abandoned' => $totalAbandoned,
            'formatted_service_time' => $this->formatSeconds($avgServiceSeconds ?? 0),
            'formatted_wait_time' => $this->formatSeconds($avgWaitSeconds ?? 0),
        ];

        $currentQueue = SalesQueue::waiting()->with('catalogClientType')->get()->map(function ($q) {
            return $q->toQueuePayload([
                'queued_at' => Carbon::parse($q->queued_at)->format('H:i'),
            ]);
        });

        $sellers = DailyShift::with([
            'employee',
            'catalogBreakReason',
            'servedCustomers' => function ($q) {
                $q->serving()->with('catalogClientType');
            },
        ])
            ->whereDate('work_date', today())
            ->get()
            ->map(function ($shift) {
                $currentClient = $shift->servedCustomers->first();
                $state = 'OFFLINE';
                $stateStartedAt = null;
                $clientName = null;
                $breakReason = null;
                $clientType = null;
                $clientTypeLabel = null;
                $usePremiumAlert = false;
                $hasDisability = false;

                if ($currentClient && $currentClient->started_serving_at) {
                    $state = 'SERVING';
                    $stateStartedAt = Carbon::parse($currentClient->started_serving_at)->timestamp * 1000;
                    $clientName = $currentClient->client_name;
                    $clientType = $currentClient->resolveClientTypeCode();
                    $clientTypeLabel = $currentClient->resolveClientTypeLabel();
                    $usePremiumAlert = $currentClient->catalogClientType?->usesPremiumAlert() ?? false;
                    $hasDisability = $currentClient->has_disability;
                } elseif ($shift->current_status === 'BREAK' && $shift->last_status_change_at) {
                    $state = 'BREAK';
                    $stateStartedAt = Carbon::parse($shift->last_status_change_at)->timestamp * 1000;
                    $breakReason = $shift->resolveBreakReasonLabel();
                } elseif ($shift->current_status === 'ONLINE' && $shift->last_status_change_at) {
                    $state = 'ONLINE';

                    $lastSale = SalesQueue::where('assigned_shift_id', $shift->id)
                        ->withStatusCode('COMPLETED')
                        ->latest('completed_at')
                        ->first();

                    $statusChangeTime = Carbon::parse($shift->last_status_change_at);

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
                    'client_type' => $clientType,
                    'client_type_label' => $clientTypeLabel,
                    'use_premium_alert' => $usePremiumAlert,
                    'has_disability' => $hasDisability,
                    'break_reason' => $breakReason,
                    'sales_today' => $shift->customers_served_count,
                ];
            });

        return response()->json([
            'metrics' => $metrics,
            'queue' => $currentQueue,
            'sellers' => $sellers,
        ]);
    }

    private function formatSeconds($totalSeconds)
    {
        if (!$totalSeconds || $totalSeconds <= 0) {
            return '00:00';
        }
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = round($totalSeconds % 60);

        return $hours > 0
            ? sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%02d:%02d', $minutes, $seconds);
    }
}
