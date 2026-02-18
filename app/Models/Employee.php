<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_code',
        'job_position', // <--- CORREGIDO (Antes decía 'position')
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

    // ... (El resto del archivo y relaciones se quedan igual) ...

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}