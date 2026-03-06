<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class TvAd extends Model
{
    use HasFactory;

    protected $table = 'tv_ads';

    protected $fillable = [
        'title',
        'media_path',
        'media_type',       // ENUM: IMAGE, VIDEO
        'duration_seconds', 
        'is_active',
        'start_date',
        'end_date',
        'sort_order',
    ];

    /**
     * Casting de tipos.
     * Ahora usamos datetime para incluir la hora exacta.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros Inteligentes)
    |--------------------------------------------------------------------------
    */

    public function scopeCurrentlyActive(Builder $query): void
    {
        $now = now();

        $query->where('is_active', true)
              ->where(function ($q) use ($now) {
                  $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $now);
              })
              ->where(function ($q) use ($now) {
                  $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $now);
              })
              ->orderBy('sort_order', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Accesores (Lógica de Presentación)
    |--------------------------------------------------------------------------
    */

    public function getMediaUrlAttribute(): string
    {
        if (str_starts_with($this->media_path, 'http')) {
            return $this->media_path;
        }
        return Storage::url($this->media_path);
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'VIDEO';
    }

    /**
     * Evalúa si el anuncio ya superó su fecha/hora de finalización.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
}