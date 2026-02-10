<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Pickup extends Model
{
    use HasFactory;

    /**
     * Campos asignables masivamente.
     * Observa que 'delivered_at' es editable porque se llena al momento de la entrega.
     */
    protected $fillable = [
        'ticket_folio',
        'ticket_date',
        'client_ref_id', // ID numérico del cliente (legacy)
        'client_name',
        'department',    // ENUM: AROMAS, BELLAROMA
        'pieces',
        'status',        // ENUM: IN_CUSTODY, DELIVERED
        'receiver_name',
        'is_third_party',
        'signature_path', // Ruta al archivo de imagen de la firma
        'delivered_at',
    ];

    /**
     * Casting de tipos para manejo robusto de fechas y booleanos.
     */
    protected function casts(): array
    {
        return [
            'ticket_date' => 'date',
            'pieces' => 'integer',
            'is_third_party' => 'boolean',
            'delivered_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros de Búsqueda Optimizados)
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra solo los paquetes que están en almacén (No entregados).
     * Uso: Pickup::inCustody()->get();
     */
    public function scopeInCustody(Builder $query): void
    {
        $query->where('status', 'IN_CUSTODY');
    }

    /**
     * Filtra solo los entregados.
     */
    public function scopeDelivered(Builder $query): void
    {
        $query->where('status', 'DELIVERED');
    }

    /**
     * Buscador inteligente para el Dashboard del Checador.
     * Busca por Folio O por Nombre del Cliente.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('ticket_folio', 'LIKE', "%{$term}%")
              ->orWhere('client_name', 'LIKE', "%{$term}%")
              ->orWhere('client_ref_id', 'LIKE', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de Dominio
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica si el paquete ya fue entregado.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'DELIVERED';
    }

    /**
     * Marca el paquete como entregado, guardando la evidencia.
     * Esta función encapsula la lógica de cierre.
     */
    public function markAsDelivered(string $receiverName, bool $isThirdParty, string $signaturePath): void
    {
        $this->update([
            'status' => 'DELIVERED',
            'receiver_name' => $receiverName,
            'is_third_party' => $isThirdParty,
            'signature_path' => $signaturePath,
            'delivered_at' => now(),
        ]);
    }
}