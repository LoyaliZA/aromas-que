<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Department;
use App\Models\JobPosition;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name', 
        'employee_code',
        'job_position',
        'job_position_id',
        'department', 
        'department_id',
        'appears_in_sales_queue',
        'hire_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'is_active' => 'boolean',
            'appears_in_sales_queue' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function catalogDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function catalogJobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id');
    }

    public function resolveJobPositionName(): ?string
    {
        $this->loadMissing('catalogJobPosition');

        return $this->catalogJobPosition?->name ?? ($this->attributes['job_position'] ?? null);
    }

    public function resolveDepartmentName(): ?string
    {
        $this->loadMissing('catalogDepartment');

        return $this->catalogDepartment?->name ?? ($this->attributes['department'] ?? null);
    }

    public function dailyShift(): HasMany
    {
        return $this->hasMany(DailyShift::class);
    }
    
    public function todayShift()
    {
        return $this->hasOne(DailyShift::class)->where('work_date', today());
    }

    public function scopeSellers(Builder $query)
    {
        return $query->where('appears_in_sales_queue', true)->where('is_active', true);
    }

    public function scopeSellerPosition(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereHas('catalogJobPosition', fn ($q2) => $q2->where('name', 'SELLER'));

            if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'job_position')) {
                $q->orWhere('job_position', 'SELLER');
            }
        });
    }

    public function scopeActiveSellers(Builder $query)
    {
        return $query->sellerPosition()->where('is_active', true);
    }

    public function scopeByDepartment(Builder $query, $department = null): void
    {
        if ($department && $department !== 'ALL') {
            $query->where(function ($q) use ($department) {
                $q->whereHas('catalogDepartment', function ($q2) use ($department) {
                    $q2->where('name', $department);
                });
                if (\Illuminate\Support\Facades\Schema::hasColumn('employees', 'department')) {
                    $q->orWhere('department', $department);
                }
            });
        }
    }

    public function setDepartmentAttribute(?string $value): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'department')) {
            $this->attributes['department'] = $value;
        }
        $this->attributes['department_id'] = $value ? Department::idFromName($value) : null;
    }

    public function setJobPositionAttribute(?string $value): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'job_position')) {
            $this->attributes['job_position'] = $value;
        }
        $this->attributes['job_position_id'] = $value ? JobPosition::idFromName($value) : null;
    }
}