<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TvAd extends Model
{
    use HasFactory;

    protected $table = 'tv_ads';

    const TYPE_IMAGE = 'IMAGE';
    const TYPE_VIDEO = 'VIDEO';

    protected $fillable = [
        'title',
        'media_path',
        'media_type',
        'duration_seconds',
        'is_active',
        'start_date',
        'end_date',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_seconds' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'sort_order' => 'integer',
    ];

    /**
     * Scope para obtener anuncios vigentes (Activos y dentro de rango de fechas).
     */
    public function scopeActiveNow($query)
    {
        $today = Carbon::today();
        
        return $query->where('is_active', true)
                     ->where(function($q) use ($today) {
                         $q->whereNull('start_date')
                           ->orWhere('start_date', '<=', $today);
                     })
                     ->where(function($q) use ($today) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $today);
                     })
                     ->orderBy('sort_order', 'asc');
    }
}