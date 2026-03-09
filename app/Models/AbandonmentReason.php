<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbandonmentReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
        'is_active',
    ];

    /**
     * Casteo de variables para asegurar los tipos de datos correctos.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación: Un motivo de abandono puede estar asociado a múltiples turnos en la fila.
     */
    public function salesQueues(): HasMany
    {
        return $this->hasMany(SalesQueue::class);
    }
}