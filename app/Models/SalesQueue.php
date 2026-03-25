<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class SalesQueue extends Model
{
    use HasFactory;

    /**
     * IMPORTANTE: La tabla no tiene created_at/updated_at estándar.
     * Desactivamos los timestamps automáticos para evitar errores SQL.
     */
    public $timestamps = false;

    protected $table = 'sales_queue';

    protected $fillable = [
        'customer_id',            // <-- NUEVO: Relación con el cliente registrado
        'client_name',
        'client_type',
        'has_disability',       // <-- NUEVO: Indica si el cliente tiene alguna discapacidad
        'service_type',       // SALES o CASHIER
        'turn_number',        // Número de turno asignado
        'source',             // QR_MOBILE, MANUAL_KIOSK
        'status',             // WAITING, SERVING, COMPLETED, ABANDONED
        'abandonment_reason_id',  // <-- NUEVO: Motivo de abandono si aplica
        'assigned_shift_id',  // El turno del vendedor que lo atiende
        'queued_at',
        'started_serving_at',
        'completed_at',
        'last_extended_at',
        'extension_count',
        'custom_abandonment_reason', // <-- NUEVO: Texto libre para motivo de abandono personalizado
    ];

    /**
     * Casting de fechas para que Carbon las maneje automáticamente.
     */
    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'started_serving_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_extended_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function assignedShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class, 'assigned_shift_id');
    }

    /**
     * Relación: Un turno en la fila pertenece a un cliente específico.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación: Un turno puede tener asociado un motivo de abandono.
     */
    public function abandonmentReason(): BelongsTo
    {
        return $this->belongsTo(AbandonmentReason::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Consultas Pre-fabricadas)
    |--------------------------------------------------------------------------
    */

    public function scopeWaiting(Builder $query): void
    {
        $query->where('status', 'WAITING')
            // 1. VIPs van primero (1)
            // 2. Discapacidad va después (2)
            // 3. Regulares van al final (3)
            ->orderByRaw("CASE 
                                WHEN client_type = 'VIP' THEN 1 
                                WHEN has_disability = 1 THEN 2 
                                ELSE 3 
                            END")
            // Dentro de cada grupo de prioridad, se respeta quién llegó primero
            ->orderBy('queued_at', 'asc');
    }

    public function scopeServing(Builder $query): void
    {
        $query->where('status', 'SERVING');
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereDate('queued_at', today());
    }

    public function scopeSales(Builder $query): void
    {
        $query->where('service_type', 'SALES');
    }

    public function scopeCashier(Builder $query): void
    {
        $query->where('service_type', 'CASHIER');
    }

    /**
     * Relación: Un turno en la fila puede tener múltiples calificaciones (Cliente y Vendedor).
     */
    public function ratings()
    {
        return $this->hasMany(SaleRating::class, 'sales_queue_id');
    }
}
