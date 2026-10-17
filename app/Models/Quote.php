<?php

namespace App\Models;

use App\Models\Concerns\HasLineItems;
use App\Models\Concerns\HasNumber;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasLineItems, HasNumber;

    public const NUMBER_PREFIX = 'DEV';

    public const STATUSES = [
        'brouillon' => ['Brouillon', 'gray'],
        'envoye' => ['Envoyé', 'blue'],
        'accepte' => ['Accepté', 'emerald'],
        'refuse' => ['Refusé', 'red'],
        'facture' => ['Facturé', 'violet'],
    ];

    protected $fillable = [
        'number', 'client_id', 'date', 'valid_until', 'status', 'subject',
        'discount', 'tax_rate', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'float', 'discount' => 'float', 'tax_rate' => 'float',
            'tax_amount' => 'float', 'total' => 'float',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->isExpired()) {
            return 'orange';
        }

        return self::STATUSES[$this->status][1] ?? 'gray';
    }

    public function isExpired(): bool
    {
        return in_array($this->status, ['brouillon', 'envoye'])
            && $this->valid_until && $this->valid_until->isPast();
    }
}
