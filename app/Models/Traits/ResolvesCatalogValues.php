<?php

namespace App\Models\Traits;

trait ResolvesCatalogValues
{
    protected function legacyColumn(string $column): mixed
    {
        return $this->attributes[$column] ?? null;
    }

    protected static function matchCatalogCode(?int $id, ?string $legacy, callable $idFromCode, string $code): bool
    {
        if ($id !== null) {
            return $idFromCode($code) === $id;
        }

        return strtoupper((string) $legacy) === strtoupper($code);
    }
}
