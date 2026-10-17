<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Données minimales : compte administrateur + paramètres par défaut.
     * Données de démonstration : php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@gc-manager.local')],
            ['name' => env('ADMIN_NAME', 'Administrateur'), 'password' => Hash::make(env('ADMIN_PASSWORD', 'password'))]
        );

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        \Illuminate\Support\Facades\Cache::forget('settings');
    }
}
