<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class CustodyDurationFormatter
{
    public static function label(
        CarbonInterface|string $from,
        CarbonInterface|string|null $to = null,
        string $prefix = 'Hace '
    ): string {
        $from = Carbon::parse($from);
        $to = $to ? Carbon::parse($to) : now();

        if ($from->greaterThan($to)) {
            return $prefix . 'recién registrado';
        }

        if ($from->isSameDay($to)) {
            return $prefix . 'hoy';
        }

        $diff = $from->diff($to);
        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y === 1 ? '1 año' : "{$diff->y} años";
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m === 1 ? '1 mes' : "{$diff->m} meses";
        }
        if ($diff->d > 0) {
            $parts[] = $diff->d === 1 ? '1 día' : "{$diff->d} días";
        }

        if (empty($parts)) {
            return $prefix . 'menos de 1 día';
        }

        return $prefix . implode(', ', $parts);
    }
}
