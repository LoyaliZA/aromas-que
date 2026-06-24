<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftStatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'daily_shift_id',
        'previous_status',
        'new_status',
        'reason',
        'break_reason_id',
        'changed_at',
        'approved_by_id',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function dailyShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class);
    }

    public function catalogBreakReason(): BelongsTo
    {
        return $this->belongsTo(BreakReason::class, 'break_reason_id');
    }

    public function resolveReasonCode(): ?string
    {
        $this->loadMissing('catalogBreakReason');

        return $this->catalogBreakReason?->code ?? $this->reason;
    }

    public function resolveReasonLabel(): string
    {
        $this->loadMissing('catalogBreakReason');

        return $this->catalogBreakReason?->label ?? ($this->reason ?? 'General');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }
}
