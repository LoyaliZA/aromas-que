<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupLogistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_id', 'bank_id', 'warehouse_id', 'courier_id',
        'note_amount', 'credit_balance', 'tracking_number',
        'box_number', 'is_local_delivery', 'is_store_pickup'
    ];

    protected function casts(): array
    {
        return [
            'note_amount' => 'decimal:2',
            'credit_balance' => 'decimal:2',
            'is_local_delivery' => 'boolean',
            'is_store_pickup' => 'boolean',
        ];
    }

    public function pickup(): BelongsTo { return $this->belongsTo(Pickup::class); }
    public function bank(): BelongsTo { return $this->belongsTo(Bank::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function courier(): BelongsTo { return $this->belongsTo(Courier::class); }
}