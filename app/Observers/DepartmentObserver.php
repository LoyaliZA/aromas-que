<?php

namespace App\Observers;

use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentObserver
{
    public function saved(Department $department): void
    {
        Department::forgetNameCache($department->name);

        if ($department->isDirty('name') && $department->getOriginal('name')) {
            $oldName = $department->getOriginal('name');
            Department::forgetNameCache($oldName);

            DB::table('employees')->where('department', $oldName)->update(['department' => $department->name]);
            DB::table('pickups')->where('department', $oldName)->update(['department' => $department->name]);
        }
    }

    public function deleted(Department $department): void
    {
        Department::forgetNameCache($department->name);
    }
}
