<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DailyShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_date',
        'current_status',
        'break_reason',
        'break_reason_id',
        'has_taken_lunch',
        'lunch_seconds_left',
        'flagged_as_idle',
        'customers_served_count',
        'last_status_change_at',
        'last_action_at',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'has_taken_lunch' => 'boolean',
            'lunch_seconds_left' => 'integer',
            'flagged_as_idle' => 'boolean',
            'last_status_change_at' => 'datetime',
            'last_action_at' => 'datetime',
            'customers_served_count' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function catalogBreakReason(): BelongsTo
    {
        return $this->belongsTo(BreakReason::class, 'break_reason_id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ShiftStatusLog::class);
    }

    public function servedCustomers(): HasMany
    {
        return $this->hasMany(SalesQueue::class, 'assigned_shift_id');
    }

    public function resolveBreakReasonCode(): ?string
    {
        $this->loadMissing('catalogBreakReason');

        return $this->catalogBreakReason?->code ?? ($this->attributes['break_reason'] ?? null);
    }

    public function resolveBreakReasonLabel(): string
    {
        $this->loadMissing('catalogBreakReason');

        return $this->catalogBreakReason?->label ?? ($this->attributes['break_reason'] ?? 'General');
    }

    public function isLunchBreak(): bool
    {
        $this->loadMissing('catalogBreakReason');

        if ($this->catalogBreakReason) {
            return $this->catalogBreakReason->is_lunch;
        }

        return ($this->attributes['break_reason'] ?? null) === 'LUNCH';
    }

    public static function breakReasonAttributes(?BreakReason $breakReason): array
    {
        if (!$breakReason) {
            return ['break_reason_id' => null];
        }

        $attrs = ['break_reason_id' => $breakReason->id];
        if (\Illuminate\Support\Facades\Schema::hasColumn('daily_shifts', 'break_reason')) {
            $attrs['break_reason'] = $breakReason->code;
        }

        return $attrs;
    }

    public function scopeAvailable(Builder $query): void
    {
        $servingId = QueueStatus::idFromCode('SERVING');
        $hasLegacyStatus = \Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'status');

        $query->where('current_status', 'ONLINE')
            ->where('flagged_as_idle', false)
            ->whereNotExists(function ($sub) use ($servingId, $hasLegacyStatus) {
                $sub->select(DB::raw(1))
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
            });
    }

    public static function assignNextAgent(): ?self
    {
        $candidates = self::available()->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $totalSalesToday = $candidates->sum('customers_served_count');

        if ($totalSalesToday === 0) {
            return $candidates->random();
        }

        return $candidates->sortBy('last_status_change_at')->first();
    }

    public function isAvailable(): bool
    {
        return $this->current_status === 'ONLINE' && ! $this->flagged_as_idle;
    }

    public function isOnBreak(): bool
    {
        return $this->current_status === 'BREAK';
    }

    public function touchLastAction(): void
    {
        $this->update(['last_action_at' => now()]);
    }
}
