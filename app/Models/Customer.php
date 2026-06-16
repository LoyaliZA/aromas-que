<?php

namespace App\Models;

use App\Models\Traits\ResolvesClientTypeMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use HasFactory, ResolvesClientTypeMetadata;

    protected $fillable = [
        'customer_number',
        'name',
        'client_type',
        'client_type_id',
        'phone',
        'email',
    ];

    public function salesQueues(): HasMany
    {
        return $this->hasMany(SalesQueue::class);
    }

    public function catalogClientType(): BelongsTo
    {
        return $this->belongsTo(ClientType::class, 'client_type_id');
    }

    public function scopeByClientType(Builder $query, $clientType = null): void
    {
        if ($clientType && $clientType !== 'ALL') {
            $code = ClientType::normalizeInput($clientType);
            $query->where(function ($q) use ($code, $clientType) {
                $q->whereHas('catalogClientType', fn ($q2) => $q2->where('code', $code));
                if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'client_type')) {
                    $q->orWhere('client_type', $code)->orWhere('client_type', $clientType);
                }
            });
        }
    }

    public function setClientTypeAttribute(?string $value): void
    {
        $type = $value ? ClientType::resolveFromInput($value) : null;
        $code = $type?->code;

        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'client_type')) {
            $this->attributes['client_type'] = $code;
        }
        $this->attributes['client_type_id'] = $type?->id ?? ClientType::defaultId();
    }
}
