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
        'department',    // Aromas o Bellaroma
        'pieces',
        'status',        // Si esta entregado o no (IN_CUSTODY, DELIVERED)
        'receiver_name',
        'is_third_party',
        'signature_path', // Ruta al archivo de imagen de la firma
        'delivered_at',
        'notes',         // Campo de comentarios
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
    | Scopes (Filtros de Búsqueda)
    |--------------------------------------------------------------------------
    */

    public function scopeInCustody(Builder $query): void
    {
        $query->where('status', 'IN_CUSTODY');
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereDate('created_at', today());
    }

    /**
     * Buscador "Amplio" (Insensible a acentos y mayúsculas).
     */
    public function scopeSearch(Builder $query, $term): void
    {
        if ($term) {
            $query->where(function($q) use ($term) {
                // Usamos 'utf8mb4_general_ci' para que á = a, É = e, etc.
                $collation = 'utf8mb4_general_ci';
                
                $q->whereRaw("ticket_folio COLLATE $collation LIKE ?", ["%{$term}%"])
                  ->orWhereRaw("client_name COLLATE $collation LIKE ?", ["%{$term}%"])
                  ->orWhereRaw("client_ref_id COLLATE $collation LIKE ?", ["%{$term}%"]);
            });
        }
    }

    /**
     * Filtro por rango de fechas (Opcional).
     */
    public function scopeByDate(Builder $query, $start = null, $end = null): void
    {
        if ($start) {
            $query->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $query->whereDate('created_at', '<=', $end);
        }
    }

    /**
     * Filtro por estado específico.
     */
    public function scopeByStatus(Builder $query, $status = null): void
    {
        if ($status && $status !== 'ALL') {
            $query->where('status', $status);
        }
    }

    /**
     * Filtro por Departamento (Aromas / Bellaroma).
     */
    public function scopeByDepartment(Builder $query, $department = null): void
    {
        if ($department && $department !== 'ALL') {
            $query->where('department', $department);
        }
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