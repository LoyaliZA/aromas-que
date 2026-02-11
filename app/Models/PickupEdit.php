<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupEdit extends Model
{
    use HasFactory;

    protected $table = 'pickup_edits';

    protected $fillable = [
        'pickup_id',
        'user_id',
        'changes',
        'reason',
    ];

    protected $casts = [
        'changes' => 'array', // Para que el JSON se convierta en array automáticamente
    ];

    // Relación inversa (opcional)
    public function pickup()
    {
        return $this->belongsTo(Pickup::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}