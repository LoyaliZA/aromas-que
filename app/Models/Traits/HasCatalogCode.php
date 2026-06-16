<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait HasCatalogCode
{
    abstract protected static function catalogCodeCachePrefix(): string;

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function idFromCode(string $code): ?int
    {
        $code = strtoupper(trim($code));
        $prefix = static::catalogCodeCachePrefix();

        return Cache::rememberForever("{$prefix}_{$code}", function () use ($code) {
            return static::where('code', $code)->value('id');
        });
    }

    public static function forgetCodeCache(string $code): void
    {
        Cache::forget(static::catalogCodeCachePrefix() . '_' . strtoupper(trim($code)));
    }

    public static function labelsByCode(): array
    {
        return static::active()->orderBy('sort_order')->pluck('label', 'code')->all();
    }
}
