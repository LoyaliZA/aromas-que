<?php

namespace App\Observers;

use App\Models\BreakReason;
use Illuminate\Support\Facades\DB;

class BreakReasonObserver
{
    public function saved(BreakReason $breakReason): void
    {
        BreakReason::forgetCodeCache($breakReason->code);

        if ($breakReason->isDirty('code') && $breakReason->getOriginal('code')) {
            $oldCode = $breakReason->getOriginal('code');
            BreakReason::forgetCodeCache($oldCode);

            if (\Illuminate\Support\Facades\Schema::hasColumn('daily_shifts', 'break_reason')) {
                DB::table('daily_shifts')->where('break_reason', $oldCode)->update(['break_reason' => $breakReason->code]);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('shift_status_logs', 'reason')) {
                DB::table('shift_status_logs')->where('reason', $oldCode)->update(['reason' => $breakReason->code]);
            }
        }
    }

    public function deleted(BreakReason $breakReason): void
    {
        BreakReason::forgetCodeCache($breakReason->code);
    }
}
