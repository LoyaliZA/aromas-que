<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyShift extends Model
{
    use HasFactory;

    /**
     * Definimos los campos editables.
     * Importante: 'last_action_at' es vital para el "Heartbeat" del sistema
     * (saber si el usuario sigue ahí).
     */
    protected $fillable = [
        'employee_id',
        'work_date',
        'current_status',       // ONLINE, BREAK, BUSY, OFFLINE
        'flagged_as_idle',      // Si el sistema detectó abandono (True/False)
        'customers_served_count',
        'last_status_change_at',
        'last_action_at',
    ];

    /**
     * Casting de tipos nativos.
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'flagged_as_idle' => 'boolean',
            'last_status_change_at' => 'datetime',
            'last_action_at' => 'datetime',
            'customers_served_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * El turno pertenece a un Empleado.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Un turno tiene una bitácora de cambios de estado.
     * Ejemplo: De ONLINE pasó a BUSY a las 10:00am.
     * (Crearemos este modelo en el siguiente paso).
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ShiftStatusLog::class);
    }

    /**
     * Un turno tiene múltiples clientes atendidos (Ventas).
     * Relación con la tabla 'sales_queue'.
     */
    public function servedCustomers(): HasMany
    {
        return $this->hasMany(SalesQueue::class, 'assigned_shift_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de Dominio (State Machine Helpers)
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica si el vendedor está disponible para recibir clientes.
     */
    public function isAvailable(): bool
    {
        return $this->current_status === 'ONLINE' && ! $this->flagged_as_idle;
    }

    /**
     * Verifica si está en descanso.
     */
    public function isOnBreak(): bool
    {
        return $this->current_status === 'BREAK';
    }

    /**
     * Actualiza el "latido" del usuario para evitar que se marque como inactivo.
     */
    public function touchLastAction(): void
    {
        $this->update(['last_action_at' => now()]);
    }
}