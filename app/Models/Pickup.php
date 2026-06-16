<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\PickupStatus;
use App\Models\Department;

class Pickup extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_folio',
        'ticket_date',
        'client_ref_id',
        'client_name',
        'department',
        'department_id',
        'amount',         
        'balance',        
        'seller_id',      
        'pieces',
        'bags',           // <-- NUEVO CAMPO
        'box_type_id',    
        'status_id',
        'notes',
        'correction_notes',
        'receiver_name',
        'is_third_party',
        'signature_path',
        'evidence_path',
        'initial_evidence_path', // <-- NUEVO
        'package_evidence_path', // <-- NUEVO
        'is_complementary',      // <-- NUEVO
        'parent_pickup_id',      // <-- NUEVO
        'received_by_checker_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'ticket_date' => 'date',
            'pieces' => 'integer',
            'is_third_party' => 'boolean',
            'received_by_checker_at' => 'datetime', // <-- NUEVO CAST
            'delivered_at' => 'datetime',
        ];
    }

    /* scopes omitidos por brevedad, dejamos todo igual hasta abajo */
    public function scopeInCustody(Builder $query): void
    {
        $query->whereHas('currentStatus', function ($q) {
            $q->where('code', 'IN_CUSTODY');
        });
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
    public function scopeByStatus(Builder $query, $status = null): void
    {
        if ($status && $status !== 'ALL') {
            $query->whereHas('currentStatus', function ($q) use ($status) {
                $q->where('code', $status);
            });
        }
    }
    
    public function scopeByDepartment(Builder $query, $department = null): void
    {
        if ($department && $department !== 'ALL') {
            $query->where(function ($q) use ($department) {
                $q->whereHas('catalogDepartment', function ($q2) use ($department) {
                    $q2->where('name', $department);
                });
                if (\Illuminate\Support\Facades\Schema::hasColumn('pickups', 'department')) {
                    $q->orWhere('department', $department);
                }
            });
        }
    }
    public function scopeVisibleForChecker(Builder $query): void
    {
        $query->where('created_at', '>=', now()->subDays(15)->startOfDay());
    }
    public function scopeRezagados(Builder $query): void
    {
        $query->whereHas('currentStatus', function ($q) {
            $q->where('code', 'IN_CUSTODY');
        })->where('created_at', '<', now()->subDays(15)->startOfDay());
    }

    public function isDelivered(): bool
    {
        return $this->currentStatus && $this->currentStatus->code === 'DELIVERED';
    }

    // NUEVO HELPER
    public function isReceivedByChecker(): bool
    {
        return !is_null($this->received_by_checker_at);
    }

    public function custodyDurationLabel(string $prefix = 'Hace '): string
    {
        return \App\Support\CustodyDurationFormatter::label($this->created_at, null, $prefix);
    }

    public function markAsDelivered(string $receiverName, bool $isThirdParty, string $signaturePath, ?string $evidencePath = null, ?string $notes = null): void
    {
        // Buscamos el ID del estatus mediante el código
        $statusId = PickupStatus::where('code', 'DELIVERED')->value('id');

        $data = [
            'status_id' => $statusId,
            'receiver_name' => $receiverName,
            'is_third_party' => $isThirdParty,
            'signature_path' => $signaturePath,
            'delivered_at' => now(),
        ];

        if ($evidencePath) {
            $data['evidence_path'] = $evidencePath;
        }
        if ($notes) {
            $data['notes'] = $notes;
        }
        $this->update($data);
    }

    public function logistic(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PickupLogistic::class);
    }

    public function catalogDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function getDepartmentAttribute(): ?string
    {
        $this->loadMissing('catalogDepartment');

        return $this->catalogDepartment?->name ?? ($this->attributes['department'] ?? null);
    }

    public function timelines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PickupTimeline::class)->orderBy('created_at', 'desc');
    }

    // Relación con el Cliente (Corregida con tu llave foránea exacta)
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'client_ref_id');
    }

    public function currentStatus()
    {
        return $this->belongsTo(PickupStatus::class, 'status_id');
    }

    public function seller(){
        return $this->belongsTo(Employee::class, 'seller_id');
    }

    public function boxType()
    {
        return $this->belongsTo(BoxType::class, 'box_type_id');
    }

    // Relación recursiva para obtener el resguardo original si este es complementario
    public function parentPickup()
    {
        return $this->belongsTo(Pickup::class, 'parent_pickup_id');
    }

    // Relación para obtener los complementarios que dependen de este resguardo
    public function complementaryPickups()
    {
        return $this->hasMany(Pickup::class, 'parent_pickup_id');
    }

    public function setDepartmentAttribute(?string $value): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'department')) {
            $this->attributes['department'] = $value;
        }
        $this->attributes['department_id'] = $value ? Department::idFromName($value) : null;
    }
}
