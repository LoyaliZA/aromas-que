<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttentionIncident extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_shift_id',
        'sales_queue_id',
        'employee_id',
        'customer_id',
        'turn_number',
        'client_name',
        'reason',
        'details',
    ];

    public function dailyShift(): BelongsTo
    {
        return $this->belongsTo(DailyShift::class, 'daily_shift_id');
    }

    public function salesQueue(): BelongsTo
    {
        return $this->belongsTo(SalesQueue::class, 'sales_queue_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
