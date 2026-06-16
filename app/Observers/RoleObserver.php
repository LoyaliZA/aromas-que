<?php

namespace App\Observers;

use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    public function saved(Role $role): void
    {
        Role::forgetNameCache($role->name);

        if ($role->isDirty('name') && $role->getOriginal('name')) {
            $oldName = $role->getOriginal('name');
            Role::forgetNameCache($oldName);

            DB::table('users')
                ->where('role', $oldName)
                ->update(['role' => $role->name]);
        }
    }

    public function deleted(Role $role): void
    {
        Role::forgetNameCache($role->name);
    }
}
