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
        'client_name',
        'source',             // QR_MOBILE, MANUAL_KIOSK
        'status',             // WAITING, SERVING, COMPLETED, ABANDONED
        'assigned_shift_id',  // El turno del vendedor que lo atiende
        'queued_at',
        'started_serving_at',
        'completed_at',
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
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * El cliente en fila es atendido por un Turno específico (Vendedor en ese momento).
     */
    public function assignedShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class, 'assigned_shift_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Consultas Pre-fabricadas)
    |--------------------------------------------------------------------------
    | Los Scopes permiten escribir código legible.
    | En vez de: SalesQueue::where('status', 'WAITING')->orderBy(...)->get();
    | Escribiremos: SalesQueue::waiting()->get();
    */

    public function scopeWaiting(Builder $query): void
    {
        $query->where('status', 'WAITING')
              ->orderBy('queued_at', 'asc'); // FIFO (First In, First Out)
    }

    public function scopeServing(Builder $query): void
    {
        $query->where('status', 'SERVING');
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereDate('queued_at', today());
    }
}