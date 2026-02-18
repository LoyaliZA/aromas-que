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
        'status',        // IN_CUSTODY, DELIVERED
        'receiver_name',
        'is_third_party',
        'signature_path', // Ruta a la firma
        'evidence_path',  // Agregado: Foto de evidencia de entrega
        'delivered_at',
        'notes',          // Campo de comentarios/observaciones
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

    public function scopeSearch(Builder $query, $term): void
    {
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('ticket_folio', 'like', "%{$term}%")
                  ->orWhere('client_name', 'like', "%{$term}%")
                  ->orWhere('client_ref_id', 'like', "%{$term}%");
            });
        }
    }

    public function scopeByDate(Builder $query, $start, $end): void
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
    | Scopes de Lógica de Negocio (Rezagados vs Recientes)
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra lo que el Checador puede ver (Solo registros recientes <= 15 días).
     * Por seguridad, el checador no debe ver paquetes viejos olvidados.
     */
    public function scopeVisibleForChecker(Builder $query): void
    {
        $query->where('created_at', '>=', now()->subDays(15)->startOfDay());
    }

    /**
     * Filtra los "Rezagados": Paquetes en custodia con más de 15 días de antigüedad.
     * Estos solo los debe ver el Gerente con permisos especiales.
     */
    public function scopeRezagados(Builder $query): void
    {
        $query->where('status', 'IN_CUSTODY')
              ->where('created_at', '<', now()->subDays(15)->startOfDay());
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
     * Marca el paquete como entregado, guardando la evidencia y firma.
     * Esta función encapsula toda la lógica de cierre.
     */
    public function markAsDelivered(string $receiverName, bool $isThirdParty, string $signaturePath, ?string $evidencePath = null, ?string $notes = null): void
    {
        $data = [
            'status' => 'DELIVERED',
            'receiver_name' => $receiverName,
            'is_third_party' => $isThirdParty,
            'signature_path' => $signaturePath,
            'delivered_at' => now(),
        ];

        // Solo actualizamos si nos mandan los datos (opcionales)
        if ($evidencePath) {
            $data['evidence_path'] = $evidencePath;
        }
        if ($notes) {
            $data['notes'] = $notes;
        }

        $this->update($data);
    }
}