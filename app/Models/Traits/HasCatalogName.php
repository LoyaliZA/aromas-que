<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait HasCatalogName
{
    abstract protected static function catalogCachePrefix(): string;

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function idFromName(string $name): ?int
    {
        $name = strtoupper(trim($name));
        $prefix = static::catalogCachePrefix();

        return Cache::rememberForever("{$prefix}_{$name}", function () use ($name) {
            return static::where('name', $name)->value('id');
        });
    }

    public static function forgetNameCache(string $name): void
    {
        Cache::forget(static::catalogCachePrefix() . '_' . strtoupper(trim($name)));
    }

    public static function activeNames(): array
    {
        return static::active()->orderBy('name')->pluck('name')->all();
    }
}
