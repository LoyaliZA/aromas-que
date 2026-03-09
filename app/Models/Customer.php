<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'name',
        'client_type',
        'phone',
        'email',
        // Nota: Más adelante agregaremos 'has_disability' aquí cuando hagamos la migración
    ];

    /**
     * Relación: Un cliente puede tener múltiples registros en la fila de ventas a lo largo del tiempo.
     */
    public function salesQueues(): HasMany
    {
        return $this->hasMany(SalesQueue::class);
    }
}