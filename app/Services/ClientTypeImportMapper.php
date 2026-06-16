<?php

namespace App\Services;

use App\Models\ClientType;

class ClientTypeImportMapper
{
    public function resolve(?string $rawInput): ClientType
    {
        $result = $this->resolveWithMeta($rawInput);

        return $result['type'];
    }

    /**
     * @return array{type: ClientType, recognized: bool, raw: ?string, mapped_code: string}
     */
    public function resolveWithMeta(?string $rawInput): array
    {
        if ($rawInput === null || trim($rawInput) === '') {
            return $this->defaultResult(null, false);
        }

        $key = $this->normalizeKey($rawInput);
        $map = config('client_list_import.map', []);

        if (isset($map[$key])) {
            $code = $map[$key];
            $type = ClientType::resolveFromInput($code) ?? ClientType::find(ClientType::defaultId());

            return [
                'type' => $type,
                'recognized' => true,
                'raw' => $rawInput,
                'mapped_code' => $code,
            ];
        }

        $direct = ClientType::resolveFromInput($rawInput);
        if ($direct) {
            return [
                'type' => $direct,
                'recognized' => true,
                'raw' => $rawInput,
                'mapped_code' => $direct->code,
            ];
        }

        return $this->defaultResult($rawInput, false);
    }

    public function normalizeKey(string $raw): string
    {
        $normalized = strtoupper(trim($raw));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return $normalized;
    }

    /**
     * @return array{type: ClientType, recognized: bool, raw: ?string, mapped_code: string}
     */
    private function defaultResult(?string $raw, bool $recognized): array
    {
        $defaultCode = config('client_list_import.default_code', ClientType::DEFAULT_CODE);
        $type = ClientType::resolveFromInput($defaultCode) ?? ClientType::find(ClientType::defaultId());

        return [
            'type' => $type,
            'recognized' => $recognized,
            'raw' => $raw,
            'mapped_code' => $defaultCode,
        ];
    }
}
