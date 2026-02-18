<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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
        'break_reason',         // Agregado: BATHROOM, LUNCH, ERRAND, PACKAGING
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
    | Scopes (Filtros Reutilizables)
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra solo los turnos que están "En Línea" y listos para recibir clientes.
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('current_status', 'ONLINE')
              ->where('flagged_as_idle', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de Dominio (Business Logic)
    |--------------------------------------------------------------------------
    */

    /**
     * ALGORITMO DE ASIGNACIÓN DE TURNOS
     * Determina qué vendedor debe recibir al siguiente cliente.
     * * Regla 1: Inicio del día (0 ventas globales) -> Aleatorio.
     * Regla 2: Operación normal -> El que lleve más tiempo esperando (Longest Idle).
     */
    public static function assignNextAgent(): ?self
    {
        // 1. Obtenemos todos los candidatos disponibles AHORA.
        // Usamos 'get()' para traerlos a memoria y poder evaluar la lógica compleja.
        $candidates = self::available()->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // 2. Verificamos si es el "Inicio del Turno" (Nadie ha vendido nada hoy).
        $totalSalesToday = $candidates->sum('customers_served_count');

        if ($totalSalesToday === 0) {
            // REGLA 1: Aleatorio para evitar favoritismos al llegar.
            return $candidates->random();
        }

        // 3. REGLA 2: Justicia (Longest Idle Agent).
        // Ordenamos por 'last_status_change_at' ASCENDENTE (del más viejo al más nuevo).
        // Quien haya cambiado a ONLINE hace más tiempo (ej. 10:00am) va antes
        // que quien cambió hace poco (ej. 10:05am).
        return $candidates->sortBy('last_status_change_at')->first();
    }

    /**
     * Helpers de estado individual.
     */
    public function isAvailable(): bool
    {
        return $this->current_status === 'ONLINE' && ! $this->flagged_as_idle;
    }

    public function isOnBreak(): bool
    {
        return $this->current_status === 'BREAK';
    }

    public function touchLastAction(): void
    {
        $this->update(['last_action_at' => now()]);
    }
}