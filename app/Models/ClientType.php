<?php

namespace App\Models;

use App\Models\Traits\HasCatalogCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientType extends Model
{
    use HasCatalogCode, HasFactory;

    public const DEFAULT_CODE = 'CLIENTES';

    private const LEGACY_ALIASES = [
        'REGULAR' => 'CLIENTES',
        'VIP' => 'DIAMANTE',
        'DISCAPACITY' => 'CLIENTES',
    ];

    protected $fillable = [
        'code',
        'label',
        'name',
        'sort_order',
        'prioritize_in_queue',
        'hide_on_public_tv',
        'use_premium_alert',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'prioritize_in_queue' => 'boolean',
            'hide_on_public_tv' => 'boolean',
            'use_premium_alert' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function catalogCachePrefix(): string
    {
        return 'catalog_client_type_id';
    }

    protected static function catalogCodeCachePrefix(): string
    {
        return 'catalog_client_type_code_id';
    }

    public static function idFromName(string $name): ?int
    {
        return static::idFromCode(static::normalizeInput($name));
    }

    public static function forgetNameCache(string $name): void
    {
        static::forgetCodeCache(static::normalizeInput($name));
    }

    public static function normalizeInput(string $input): string
    {
        $normalized = strtoupper(trim($input));

        return self::LEGACY_ALIASES[$normalized] ?? $normalized;
    }

    public static function resolveFromInput(?string $input): ?self
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $normalized = static::normalizeInput($input);

        return static::query()
            ->where('code', $normalized)
            ->orWhere('name', $normalized)
            ->orWhereRaw('UPPER(label) = ?', [$normalized])
            ->first();
    }

    public static function idFromInput(?string $input): ?int
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        return static::resolveFromInput($input)?->id ?? static::idFromCode(static::DEFAULT_CODE);
    }

    public static function defaultId(): ?int
    {
        return static::idFromCode(static::DEFAULT_CODE);
    }

    public function effectiveQueuePriority(): int
    {
        if (!$this->prioritize_in_queue) {
            return 999;
        }

        return (int) $this->sort_order;
    }

    public function shouldHideOnPublicTv(): bool
    {
        return (bool) $this->hide_on_public_tv;
    }

    public function usesPremiumAlert(): bool
    {
        return (bool) $this->use_premium_alert;
    }

    public function displayLabel(): string
    {
        return $this->label ?: ($this->code ?? $this->name ?? '');
    }

    public function toMetadataArray(): array
    {
        return [
            'client_type_code' => $this->code,
            'client_type_label' => $this->displayLabel(),
            'prioritize_in_queue' => (bool) $this->prioritize_in_queue,
            'hide_on_public_tv' => (bool) $this->hide_on_public_tv,
            'use_premium_alert' => (bool) $this->use_premium_alert,
        ];
    }
}
