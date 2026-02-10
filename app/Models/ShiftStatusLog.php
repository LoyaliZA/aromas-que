<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftStatusLog extends Model
{
    use HasFactory;

    /**
     * Desactivamos los timestamps automáticos de Laravel (created_at, updated_at)
     * porque esta tabla tiene su propia columna de tiempo personalizada ('changed_at').
     */
    public $timestamps = false;

    protected $fillable = [
        'daily_shift_id',
        'previous_status',
        'new_status',
        'changed_at',
    ];

    /**
     * Casting de tipos.
     * Convertimos 'changed_at' a una instancia real de Carbon (Fecha).
     */
    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Este log pertenece a un Turno Diario específico.
     */
    public function dailyShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class);
    }
}