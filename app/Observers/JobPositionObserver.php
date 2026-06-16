<?php

namespace App\Observers;

use App\Models\JobPosition;
use Illuminate\Support\Facades\DB;

class JobPositionObserver
{
    public function saved(JobPosition $jobPosition): void
    {
        JobPosition::forgetNameCache($jobPosition->name);

        if ($jobPosition->isDirty('name') && $jobPosition->getOriginal('name')) {
            $oldName = $jobPosition->getOriginal('name');
            JobPosition::forgetNameCache($oldName);

            DB::table('employees')->where('job_position', $oldName)->update(['job_position' => $jobPosition->name]);
        }
    }

    public function deleted(JobPosition $jobPosition): void
    {
        JobPosition::forgetNameCache($jobPosition->name);
    }
}
