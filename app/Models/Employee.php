<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'full_name',
        'employee_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relacion: Un empleado tiene muchos turnos diarios (historico).
     */
    public function dailyShifts(): HasMany
    {
        return $this->hasMany(DailyShift::class, 'employee_id');
    }

    /**
     * Scope para obtener solo empleados activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}