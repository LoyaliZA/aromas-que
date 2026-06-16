<?php

namespace App\Models\Traits;

trait ResolvesClientTypeMetadata
{
    public function resolveClientTypeCode(): ?string
    {
        $this->loadMissing('catalogClientType');

        return $this->catalogClientType?->code
            ?? ($this->attributes['client_type'] ?? null);
    }

    public function resolveClientTypeLabel(): ?string
    {
        $this->loadMissing('catalogClientType');

        if ($this->catalogClientType) {
            return $this->catalogClientType->displayLabel();
        }

        $legacy = $this->attributes['client_type'] ?? null;

        return $legacy ? \App\Models\ClientType::resolveFromInput($legacy)?->displayLabel() ?? $legacy : null;
    }

    public function resolveClientTypeName(): ?string
    {
        return $this->resolveClientTypeLabel();
    }

    public function clientTypeMetadata(): array
    {
        $this->loadMissing('catalogClientType');

        if ($this->catalogClientType) {
            return $this->catalogClientType->toMetadataArray();
        }

        $legacy = $this->attributes['client_type'] ?? null;
        $resolved = $legacy ? \App\Models\ClientType::resolveFromInput($legacy) : null;

        if ($resolved) {
            return $resolved->toMetadataArray();
        }

        return [
            'client_type_code' => \App\Models\ClientType::DEFAULT_CODE,
            'client_type_label' => 'Clientes',
            'prioritize_in_queue' => false,
            'hide_on_public_tv' => false,
            'use_premium_alert' => false,
        ];
    }
}
