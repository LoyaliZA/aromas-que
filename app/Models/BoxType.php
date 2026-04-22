<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }
}