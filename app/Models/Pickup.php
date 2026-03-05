<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Pickup extends Model
{
    use HasFactory;

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
        'evidence_path',
        'received_by_checker_at', // <-- NUEVO: Fecha de confirmación en almacén
        'delivered_at',
        'notes',
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
    public function scopeInCustody(Builder $query): void { $query->where('status', 'IN_CUSTODY'); }
    public function scopeSearch(Builder $query, $term): void {
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('ticket_folio', 'like', "%{$term}%")
                  ->orWhere('client_name', 'like', "%{$term}%")
                  ->orWhere('client_ref_id', 'like', "%{$term}%");
            });
        }
    }
    public function scopeByDate(Builder $query, $start, $end): void {
        if ($start) { $query->whereDate('created_at', '>=', $start); }
        if ($end) { $query->whereDate('created_at', '<=', $end); }
    }
    public function scopeByStatus(Builder $query, $status = null): void {
        if ($status && $status !== 'ALL') { $query->where('status', $status); }
    }
    public function scopeByDepartment(Builder $query, $department = null): void {
        if ($department && $department !== 'ALL') { $query->where('department', $department); }
    }
    public function scopeVisibleForChecker(Builder $query): void {
        $query->where('created_at', '>=', now()->subDays(15)->startOfDay());
    }
    public function scopeRezagados(Builder $query): void {
        $query->where('status', 'IN_CUSTODY')->where('created_at', '<', now()->subDays(15)->startOfDay());
    }

    public function isDelivered(): bool { return $this->status === 'DELIVERED'; }
    
    // NUEVO HELPER
    public function isReceivedByChecker(): bool {
        return !is_null($this->received_by_checker_at);
    }

    public function markAsDelivered(string $receiverName, bool $isThirdParty, string $signaturePath, ?string $evidencePath = null, ?string $notes = null): void
    {
        $data = [
            'status' => 'DELIVERED',
            'receiver_name' => $receiverName,
            'is_third_party' => $isThirdParty,
            'signature_path' => $signaturePath,
            'delivered_at' => now(),
        ];
        if ($evidencePath) { $data['evidence_path'] = $evidencePath; }
        if ($notes) { $data['notes'] = $notes; }
        $this->update($data);
    }
}