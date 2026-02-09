<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQueue extends Model
{
    use HasFactory;

    protected $table = 'sales_queue'; // Nombre singular en DB

    // Estatus
    const STATUS_WAITING = 'WAITING';
    const STATUS_SERVING = 'SERVING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_ABANDONED = 'ABANDONED';

    // Origen
    const SOURCE_QR = 'QR_MOBILE';
    const SOURCE_KIOSK = 'MANUAL_KIOSK';

    protected $fillable = [
        'client_name',
        'source',
        'status',
        'assigned_shift_id',
        'queued_at',
        'started_serving_at',
        'completed_at',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'started_serving_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relacion: Atencion asignada a un turno especifico.
     */
    public function assignedShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class, 'assigned_shift_id');
    }

    /**
     * Scope para clientes en espera.
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATUS_WAITING)
                     ->orderBy('queued_at', 'asc');
    }
}