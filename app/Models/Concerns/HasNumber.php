<?php

namespace App\Models\Concerns;

/**
 * Génère des numéros séquentiels par année : FAC-2026-0001
 */
trait HasNumber
{
    public static function nextNumber(): string
    {
        $prefix = static::NUMBER_PREFIX.'-'.now()->format('Y').'-';

        $last = static::query()
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('number')
            ->value('number');

        $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
