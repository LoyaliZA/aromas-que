<?php

namespace App\Observers;

use App\Models\QueueSource;
use Illuminate\Support\Facades\DB;

class QueueSourceObserver
{
    public function saved(QueueSource $queueSource): void
    {
        QueueSource::forgetCodeCache($queueSource->code);

        if ($queueSource->isDirty('code') && $queueSource->getOriginal('code')) {
            $oldCode = $queueSource->getOriginal('code');
            QueueSource::forgetCodeCache($oldCode);

            if (\Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'source')) {
                DB::table('sales_queue')->where('source', $oldCode)->update(['source' => $queueSource->code]);
            }
        }
    }

    public function deleted(QueueSource $queueSource): void
    {
        QueueSource::forgetCodeCache($queueSource->code);
    }
}
