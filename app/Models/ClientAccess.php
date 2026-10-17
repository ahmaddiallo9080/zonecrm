<?php

namespace App\Models;

use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class ClientAccess extends Model
{
    protected $fillable = [
        'client_id', 'type', 'label', 'host', 'port', 'username', 'password',
        'serial_number', 'plan', 'renewal_date', 'extra',
    ];

    protected $hidden = ['password', 'extra'];

    protected function casts(): array
    {
        return [
            // Chiffrés en base avec APP_KEY : ne jamais perdre/modifier la clé en production.
            'password' => 'encrypted',
            'extra' => 'encrypted',
            'renewal_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        // Pas de chaîne vide chiffrée : un secret vide reste NULL
        static::saving(function (ClientAccess $access) {
            foreach (['password', 'extra'] as $field) {
                if ($access->isDirty($field) && blank($access->{$field})) {
                    $access->{$field} = null;
                }
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return Options::label(Options::ACCESS_TYPES, $this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return Options::ACCESS_TYPES[$this->type][1] ?? 'gray';
    }

    public function getRenewalSoonAttribute(): bool
    {
        return $this->renewal_date && $this->renewal_date->lte(now()->addDays(7));
    }
}
