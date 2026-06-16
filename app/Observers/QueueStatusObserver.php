<?php

namespace App\Observers;

use App\Models\QueueStatus;
use Illuminate\Support\Facades\DB;

class QueueStatusObserver
{
    public function saved(QueueStatus $queueStatus): void
    {
        QueueStatus::forgetCodeCache($queueStatus->code);

        if ($queueStatus->isDirty('code') && $queueStatus->getOriginal('code')) {
            $oldCode = $queueStatus->getOriginal('code');
            QueueStatus::forgetCodeCache($oldCode);

            if (\Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'status')) {
                DB::table('sales_queue')->where('status', $oldCode)->update(['status' => $queueStatus->code]);
            }
        }
    }

    public function deleted(QueueStatus $queueStatus): void
    {
        QueueStatus::forgetCodeCache($queueStatus->code);
    }
}
