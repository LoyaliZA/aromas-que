<?php

namespace App\Services;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Role;

class CatalogResolver
{
    public static function resolveAccessRole(string $departmentName, string $jobPositionName): string
    {
        $logisticsDepartments = Department::whereIn('name', ['BELLAROMA', 'CALLCENTER', 'CEDIS'])
            ->active()
            ->pluck('name')
            ->all();

        if (in_array($departmentName, $logisticsDepartments, true)) {
            return $departmentName;
        }

        return $jobPositionName;
    }

    public static function validateActiveCatalogName(string $table, string $value): bool
    {
        return match ($table) {
            'roles' => Role::active()->where('name', $value)->exists(),
            'job_positions' => JobPosition::active()->where('name', $value)->exists(),
            'departments' => Department::active()->where('name', $value)->exists(),
            default => false,
        };
    }
}
