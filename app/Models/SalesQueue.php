<?php

namespace App\Models;

use App\Models\Traits\ResolvesCatalogValues;
use App\Models\Traits\ResolvesClientTypeMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class SalesQueue extends Model
{
    use HasFactory, ResolvesCatalogValues, ResolvesClientTypeMetadata;

    public $timestamps = false;

    protected $table = 'sales_queue';

    protected $fillable = [
        'customer_id',
        'client_name',
        'client_type_id',
        'has_disability',
        'service_type_id',
        'turn_number',
        'source_id',
        'status_id',
        'abandonment_reason_id',
        'assigned_shift_id',
        'queued_at',
        'started_serving_at',
        'completed_at',
        'last_extended_at',
        'extension_count',
        'custom_abandonment_reason',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'started_serving_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_extended_at' => 'datetime',
        ];
    }

    public function assignedShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class, 'assigned_shift_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function abandonmentReason(): BelongsTo
    {
        return $this->belongsTo(AbandonmentReason::class);
    }

    public function catalogClientType(): BelongsTo
    {
        return $this->belongsTo(ClientType::class, 'client_type_id');
    }

    public function catalogServiceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function catalogStatus(): BelongsTo
    {
        return $this->belongsTo(QueueStatus::class, 'status_id');
    }

    public function catalogSource(): BelongsTo
    {
        return $this->belongsTo(QueueSource::class, 'source_id');
    }

    public function resolveServiceTypeName(): ?string
    {
        $this->loadMissing('catalogServiceType');

        return $this->catalogServiceType?->name ?? $this->legacyColumn('service_type');
    }

    public function resolveStatusCode(): ?string
    {
        $this->loadMissing('catalogStatus');

        return $this->catalogStatus?->code ?? $this->legacyColumn('status');
    }

    public function resolveSourceCode(): ?string
    {
        $this->loadMissing('catalogSource');

        return $this->catalogSource?->code ?? $this->legacyColumn('source');
    }

    public function scopeWithStatusCode(Builder $query, string $code): void
    {
        if (self::hasLegacyColumn('status')) {
            $statusId = QueueStatus::idFromCode($code);
            $query->where(function ($q) use ($code, $statusId) {
                if ($statusId) {
                    $q->where('status_id', $statusId);
                }
                $q->orWhere('status', $code);
            });

            return;
        }

        $query->whereHas('catalogStatus', fn ($q) => $q->where('code', $code));
    }

    public function scopeWaiting(Builder $query): void
    {
        $disabilityPriority = config('sales_queue.disability_queue_priority', 15);

        $query->withStatusCode('WAITING')
            ->leftJoin('client_types', 'sales_queue.client_type_id', '=', 'client_types.id')
            ->select('sales_queue.*')
            ->orderByRaw('LEAST(
                COALESCE(CASE WHEN client_types.prioritize_in_queue = 1 THEN client_types.sort_order END, 999),
                COALESCE(CASE WHEN sales_queue.has_disability = 1 THEN ? END, 999)
            ) ASC', [$disabilityPriority])
            ->orderBy('sales_queue.queued_at', 'asc');
    }

    public function scopeServing(Builder $query): void
    {
        $query->withStatusCode('SERVING');
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereDate('queued_at', today());
    }

    public function scopeSales(Builder $query): void
    {
        if (self::hasLegacyColumn('service_type')) {
            $salesId = ServiceType::idFromName('SALES');
            $query->where(function ($q) use ($salesId) {
                if ($salesId) {
                    $q->where('service_type_id', $salesId);
                }
                $q->orWhere('service_type', 'SALES')
                    ->orWhereHas('catalogServiceType', fn ($q2) => $q2->where('name', 'SALES'));
            });

            return;
        }

        $query->whereHas('catalogServiceType', fn ($q) => $q->where('name', 'SALES'));
    }

    public function scopeCashier(Builder $query): void
    {
        if (self::hasLegacyColumn('service_type')) {
            $cashierId = ServiceType::idFromName('CASHIER');
            $query->where(function ($q) use ($cashierId) {
                if ($cashierId) {
                    $q->where('service_type_id', $cashierId);
                }
                $q->orWhere('service_type', 'CASHIER')
                    ->orWhereHas('catalogServiceType', fn ($q2) => $q2->where('name', 'CASHIER'));
            });

            return;
        }

        $query->whereHas('catalogServiceType', fn ($q) => $q->where('name', 'CASHIER'));
    }

    public function scopeByClientType(Builder $query, $clientType = null): void
    {
        if ($clientType && $clientType !== 'ALL') {
            $code = ClientType::normalizeInput($clientType);
            $query->where(function ($q) use ($code, $clientType) {
                $q->whereHas('catalogClientType', fn ($q2) => $q2->where('code', $code));
                if (self::hasLegacyColumn('client_type')) {
                    $q->orWhere('client_type', $code)->orWhere('client_type', $clientType);
                }
            });
        }
    }

    public function scopeByServiceType(Builder $query, $serviceType = null): void
    {
        if ($serviceType && $serviceType !== 'ALL') {
            $query->where(function ($q) use ($serviceType) {
                $q->whereHas('catalogServiceType', fn ($q2) => $q2->where('name', $serviceType));
                if (self::hasLegacyColumn('service_type')) {
                    $q->orWhere('service_type', $serviceType);
                }
            });
        }
    }

    public function ratings()
    {
        return $this->hasMany(SaleRating::class, 'sales_queue_id');
    }

    public static function attributesForStatus(string $code): array
    {
        $attrs = ['status_id' => QueueStatus::idFromCode($code)];
        if (self::hasLegacyColumn('status')) {
            $attrs['status'] = $code;
        }

        return $attrs;
    }

    public static function attributesForSource(string $code): array
    {
        $attrs = ['source_id' => QueueSource::idFromCode($code)];
        if (self::hasLegacyColumn('source')) {
            $attrs['source'] = $code;
        }

        return $attrs;
    }

    public static function attributesForServiceType(string $name): array
    {
        $attrs = ['service_type_id' => ServiceType::idFromName($name)];
        if (self::hasLegacyColumn('service_type')) {
            $attrs['service_type'] = $name;
        }

        return $attrs;
    }

    public static function attributesForClientType(?string $input): array
    {
        $type = $input ? ClientType::resolveFromInput($input) : null;
        $code = $type?->code;

        $attrs = ['client_type_id' => $type?->id ?? ClientType::defaultId()];
        if (self::hasLegacyColumn('client_type')) {
            $attrs['client_type'] = $code ?? ClientType::DEFAULT_CODE;
        }

        return $attrs;
    }

    public function toQueuePayload(array $extra = []): array
    {
        return array_merge([
            'id' => $this->id,
            'turn_number' => $this->turn_number,
            'client_name' => $this->client_name,
            'client_type' => $this->resolveClientTypeCode(),
            'client_type_label' => $this->resolveClientTypeLabel(),
            'has_disability' => (bool) $this->has_disability,
            'queued_at' => $this->queued_at,
        ], $this->clientTypeMetadata(), $extra);
    }

    protected static function hasLegacyColumn(string $column): bool
    {
        static $columns = null;
        $columns ??= Schema::getColumnListing('sales_queue');

        return in_array($column, $columns, true);
    }
}
