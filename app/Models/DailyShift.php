<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyShift extends Model
{
    use HasFactory;

    protected $table = 'daily_shifts';

    // Estados definidos en SRS y DB
    const STATUS_OFFLINE = 'OFFLINE';
    const STATUS_ONLINE = 'ONLINE'; // En Linea / Disponible
    const STATUS_BUSY = 'BUSY';     // Atendiendo cliente (derivado de sales_queue)
    const STATUS_BREAK = 'BREAK';   // Descanso

    protected $fillable = [
        'employee_id',
        'work_date',
        'current_status',
        'flagged_as_idle',
        'customers_served_count',
        'last_status_change_at',
        'last_action_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'flagged_as_idle' => 'boolean',
        'last_status_change_at' => 'datetime',
        'last_action_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacion: El turno pertenece a un empleado.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Relacion: Un turno puede tener muchas atenciones asignadas.
     */
    public function salesQueues(): HasMany
    {
        return $this->hasMany(SalesQueue::class, 'assigned_shift_id');
    }

    /**
     * Helper para saber si esta disponible para asignacion.
     */
    public function isAvailable(): bool
    {
        return $this->current_status === self::STATUS_ONLINE && !$this->flagged_as_idle;
    }
}