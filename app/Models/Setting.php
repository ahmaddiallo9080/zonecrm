<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'company_name' => 'GC-Manager',
        'company_tagline' => 'Installation • Dépannage • Vente de matériel Wi-Fi Zone',
        'company_phone' => '',
        'company_email' => '',
        'company_address' => 'Conakry, Guinée',
        'company_rccm' => '',
        'company_nif' => '',
        'currency' => 'GNF',
        'currency_decimals' => '0',
        'default_tax_rate' => '0',
        'quote_validity_days' => '15',
        'invoice_due_days' => '7',
        'quote_footer' => 'Devis valable 15 jours. Acompte de 50 % à la commande.',
        'invoice_footer' => 'Merci pour votre confiance.',
    ];

    public static function all_cached(): array
    {
        return Cache::rememberForever('settings', function () {
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }
            } catch (\Throwable) {
                return [];
            }

            return static::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, $default = null)
    {
        $all = static::all_cached();

        return $all[$key] ?? $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings');
    }
}
