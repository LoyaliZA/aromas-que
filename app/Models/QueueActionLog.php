<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueActionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_queue_id',
        'user_id',
        'action_type',
        'details',
    ];

    public function salesQueue()
    {
        return $this->belongsTo(SalesQueue::class, 'sales_queue_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
