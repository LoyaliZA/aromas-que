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
        'duration_seconds', // Default: 15
        'is_active',
        'start_date',
        'end_date',
        'sort_order',
    ];

    /**
     * Casting de tipos.
     * Convertimos fechas para poder compararlas con now() fácilmente.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'duration_seconds' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros Inteligentes)
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra los anuncios que DEBEN mostrarse hoy.
     * Regla: Activo + (Fecha inicio ya pasó O es nula) + (Fecha fin no ha llegado O es nula).
     */
    public function scopeCurrentlyActive(Builder $query): void
    {
        $today = now()->startOfDay();

        $query->where('is_active', true)
              ->where(function ($q) use ($today) {
                  $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
              })
              ->where(function ($q) use ($today) {
                  $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
              })
              ->orderBy('sort_order', 'asc');
    }

    /*
    |--------------------------------------------------------------------------
    | Accesores (Formatos de Presentación)
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna la URL pública completa del archivo.
     * Uso en Blade: <img src="{{ $ad->media_url }}">
     */
    public function getMediaUrlAttribute(): string
    {
        // Si es una URL externa (http...) la regresamos tal cual, si no, buscamos en storage
        if (str_starts_with($this->media_path, 'http')) {
            return $this->media_path;
        }
        return Storage::url($this->media_path);
    }

    /**
     * Helper para saber si debemos renderizar <video> o <img>
     */
    public function isVideo(): bool
    {
        return $this->media_type === 'VIDEO';
    }
}