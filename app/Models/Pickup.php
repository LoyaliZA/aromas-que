<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    use HasFactory;

    protected $table = 'pickups';

    // Departamentos
    const DEPT_AROMAS = 'AROMAS';
    const DEPT_BELLAROMA = 'BELLAROMA';

    // Estatus
    const STATUS_IN_CUSTODY = 'IN_CUSTODY'; // En Resguardo
    const STATUS_DELIVERED = 'DELIVERED';   // Entregado

    protected $fillable = [
        'ticket_folio',
        'ticket_date',
        'client_ref_id',
        'client_name',
        'department',
        'pieces',
        'status',
        'receiver_name',
        'is_third_party',
        'signature_path',
        'delivered_at',
    ];

    protected $casts = [
        'ticket_date' => 'date',
        'pieces' => 'integer',
        'is_third_party' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    /**
     * Scope para filtrar solo los pendientes de entrega.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_IN_CUSTODY);
    }
}