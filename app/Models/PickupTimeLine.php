<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupTimeLine extends Model
{
    use HasFactory;

    protected $fillable = ['pickup_id', 'user_id', 'status', 'comment'];

    public function pickup(): BelongsTo { return $this->belongsTo(Pickup::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}