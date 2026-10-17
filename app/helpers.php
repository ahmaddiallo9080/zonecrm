<?php

use App\Models\Setting;

if (! function_exists('money')) {
    /**
     * Formate un montant : 1 250 000 GNF
     */
    function money($amount, bool $withCurrency = true): string
    {
        $decimals = (int) Setting::get('currency_decimals', 0);
        $formatted = number_format((float) $amount, $decimals, ',', ' ');

        return $withCurrency ? $formatted.' '.Setting::get('currency', 'GNF') : $formatted;
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('amount_in_words')) {
    /** "un million deux cent mille francs guinéens" (nécessite l'extension intl). */
    function amount_in_words($amount): ?string
    {
        if (! class_exists(\NumberFormatter::class)) {
            return null;
        }
        $words = (new \NumberFormatter('fr', \NumberFormatter::SPELLOUT))->format((int) round((float) $amount));
        $currency = Setting::get('currency', 'GNF') === 'GNF' ? 'francs guinéens' : Setting::get('currency');

        return ucfirst($words).' '.$currency;
    }
}

if (! function_exists('fdate')) {
    function fdate($date, string $format = 'd/m/Y'): string
    {
        return $date ? \Illuminate\Support\Carbon::parse($date)->format($format) : '—';
    }
}
