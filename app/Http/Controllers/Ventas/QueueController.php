<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\BreakReason;
use App\Models\DailyShift;
use App\Models\Employee;
use App\Models\SalesQueue;
use App\Models\ShiftStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QueueController extends Controller
{
    public function index()
    {
        $sellers = $this->getSellersList();
        $clientsWaiting = SalesQueue::waiting()->sales()->count();
        $breakReasons = BreakReason::active()->orderBy('sort_order')->get();

        return view('ventas.dashboard', compact('sellers', 'clientsWaiting', 'breakReasons'));
    }

    public function poll()
    {
        $this->runMatchmaker();

        $sellers = $this->getSellersList();
        $clientsWaiting = SalesQueue::waiting()->sales()->count();

        $recentAssignment = SalesQueue::withStatusCode('SERVING')
            ->where('turn_number', 'not like', '%-R')
            ->where('started_serving_at', '>=', now()->subSeconds(2))
            ->with(['assignedShift.employee', 'catalogClientType'])
            ->first();

        $alertData = null;
        if ($recentAssignment && $recentAssignment->assignedShift) {
            $alertData = array_merge($recentAssignment->toQueuePayload(), [
                'client' => $recentAssignment->client_name,
                'seller' => $recentAssignment->assignedShift->employee->full_name,
                'folio' => $recentAssignment->turn_number,
            ]);
        }

        $html = view('ventas.partials.sellers-grid', compact('sellers'))->render();

        return response()->json([
            'html' => $html,
            'waiting' => $clientsWaiting,
            'alert' => $alertData,
        ]);
    }

    public function toggleBreak(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:daily_shifts,id',
            'reason' => 'nullable|string',
        ]);

        $shift = DailyShift::with('catalogBreakReason')->findOrFail($request->shift_id);
        $previousStatus = $shift->current_status;

        if ($shift->current_status === 'ONLINE') {
            $reasonCode = strtoupper($request->reason ?? 'GENERAL');
            $breakReason = BreakReason::active()->where('code', $reasonCode)->first();

            if (!$breakReason) {
                return back()->with('error', 'Motivo de pausa inválido.');
            }

            if ($breakReason->is_lunch) {
                if ($shift->lunch_seconds_left <= 0) {
                    return back()->with('error', 'El tiempo de comida se ha agotado.');
                }
                $shift->has_taken_lunch = true;
                $statusChangeAt = now()->addMinutes(5);
            } else {
                $statusChangeAt = now();
            }

            $shift->update(array_merge(
                DailyShift::breakReasonAttributes($breakReason),
                [
                    'current_status' => 'BREAK',
                    'has_taken_lunch' => $shift->has_taken_lunch,
                    'last_status_change_at' => $statusChangeAt,
                ]
            ));

            ShiftStatusLog::create(array_merge(
                [
                    'daily_shift_id' => $shift->id,
                    'previous_status' => $previousStatus,
                    'new_status' => 'BREAK',
                    'changed_at' => now(),
                ],
                DailyShift::breakReasonAttributes($breakReason),
                \Illuminate\Support\Facades\Schema::hasColumn('shift_status_logs', 'reason')
                    ? ['reason' => $breakReason->code]
                    : []
            ));
        } elseif ($shift->current_status === 'BREAK') {
            if ($shift->isLunchBreak()) {
                if (now()->greaterThan($shift->last_status_change_at)) {
                    $consumedSeconds = $shift->last_status_change_at->diffInSeconds(now());
                    $shift->lunch_seconds_left = max(0, $shift->lunch_seconds_left - $consumedSeconds);
                }
            }

            $shift->update(array_merge(
                DailyShift::breakReasonAttributes(null),
                [
                    'current_status' => 'ONLINE',
                    'lunch_seconds_left' => $shift->lunch_seconds_left,
                    'last_status_change_at' => now(),
                    'last_action_at' => now(),
                ]
            ));

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
                ->serving()
                ->first();

            if ($client) {
                $client->update(array_merge(
                    SalesQueue::attributesForStatus('COMPLETED'),
                    ['completed_at' => now()]
                ));
                $shift->increment('customers_served_count');

                $shift->update([
                    'current_status' => 'RATING',
                    'last_action_at' => now(),
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
            'comments' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            if ($request->has('stars') && $request->stars > 0) {
                \App\Models\SaleRating::create([
                    'sales_queue_id' => $request->queue_id,
                    'rater_type' => 'SELLER',
                    'stars' => $request->stars,
                    'tags' => $request->tags ?? [],
                    'comments' => $request->comments,
                ]);
            }

            $shift = DailyShift::find($request->shift_id);
            if ($shift && $shift->current_status === 'RATING') {
                $shift->update([
                    'current_status' => 'ONLINE',
                    'last_action_at' => now(),
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function getRetentionList(Request $request)
    {
        $recentCompleted = SalesQueue::with(['assignedShift.employee', 'catalogClientType'])
            ->withStatusCode('COMPLETED')
            ->sales()
            ->where('completed_at', '>=', now()->subMinutes(90))
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        $availableShifts = DailyShift::with('employee')
            ->where('work_date', today())
            ->where('current_status', 'ONLINE')
            ->get()
            ->filter(function ($shift) {
                return !SalesQueue::where('assigned_shift_id', $shift->id)->serving()->exists();
            })->values();

        return response()->json([
            'clients' => $recentCompleted,
            'available_sellers' => $availableShifts,
        ]);
    }

    public function reassignRetention(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:sales_queue,id',
            'shift_id' => 'required|exists:daily_shifts,id',
        ]);

        $oldQueue = SalesQueue::find($request->queue_id);

        $baseTurn = str_replace('-R', '', $oldQueue->turn_number);
        $newTurnNumber = !empty($baseTurn) ? substr($baseTurn, 0, 7) . '-R' : 'RET-R';

        SalesQueue::create(array_merge(
            [
                'customer_id' => $oldQueue->customer_id,
                'client_name' => $oldQueue->client_name,
                'has_disability' => $oldQueue->has_disability,
                'turn_number' => $newTurnNumber,
                'assigned_shift_id' => $request->shift_id,
                'queued_at' => now(),
                'started_serving_at' => now(),
            ],
            SalesQueue::attributesForClientType($oldQueue->resolveClientTypeName()),
            SalesQueue::attributesForServiceType($oldQueue->resolveServiceTypeName() ?? 'SALES'),
            SalesQueue::attributesForSource('MANUAL_KIOSK'),
            SalesQueue::attributesForStatus('SERVING')
        ));

        DailyShift::find($request->shift_id)->update(['last_action_at' => now()]);

        return response()->json(['success' => true]);
    }

    private function getSellersList()
    {
        return Employee::sellers()->with(['todayShift.catalogBreakReason'])->get();
    }

    private function runMatchmaker()
    {
        $lock = Cache::lock('matchmaker_lock', 5);

        if (!$lock->get()) {
            return;
        }

        try {
            DB::transaction(function () {
                $waitingClients = SalesQueue::waiting()->sales()->count();
                if ($waitingClients === 0) {
                    return;
                }

                $servingId = \App\Models\QueueStatus::idFromCode('SERVING');
                $hasLegacyStatus = \Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'status');

                $availableShifts = DailyShift::where('work_date', today())
                    ->where('current_status', 'ONLINE')
                    ->whereNotExists(function ($query) use ($servingId, $hasLegacyStatus) {
                        $query->select(DB::raw(1))
                            ->from('sales_queue')
                            ->whereColumn('sales_queue.assigned_shift_id', 'daily_shifts.id')
                            ->where(function ($q) use ($servingId, $hasLegacyStatus) {
                                if ($servingId) {
                                    $q->where('sales_queue.status_id', $servingId);
                                }
                                if ($hasLegacyStatus) {
                                    $q->orWhere('sales_queue.status', 'SERVING');
                                } elseif (!$servingId) {
                                    $q->whereRaw('0 = 1');
                                }
                            });
                    })
                    ->lockForUpdate()
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

                if ($freeShifts->isEmpty()) {
                    return;
                }

                $freeShifts = $freeShifts->sortBy(function ($shift) {
                    return $shift->last_action_at ?? '2000-01-01 00:00:00';
                });

                foreach ($freeShifts as $shift) {
                    $nextClient = SalesQueue::waiting()->sales()->lockForUpdate()->first();
                    if ($nextClient) {
                        $nextClient->update(array_merge(
                            SalesQueue::attributesForStatus('SERVING'),
                            [
                                'assigned_shift_id' => $shift->id,
                                'started_serving_at' => now(),
                            ]
                        ));

                        $shift->update([
                            'flagged_as_idle' => false,
                            'last_action_at' => now(),
                        ]);
                    } else {
                        break;
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error crítico en el Matchmaker: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }
}
