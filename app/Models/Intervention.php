<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    public const TYPES = [
        'installation' => 'Installation',
        'depannage' => 'Dépannage',
        'maintenance' => 'Maintenance',
        'configuration' => 'Configuration',
        'visite' => 'Visite technique',
    ];

    public const STATUSES = [
        'planifiee' => ['Planifiée', 'blue'],
        'en_cours' => ['En cours', 'amber'],
        'terminee' => ['Terminée', 'emerald'],
        'annulee' => ['Annulée', 'gray'],
    ];

    protected $fillable = [
        'client_id', 'type', 'title', 'description', 'scheduled_at', 'done_at',
        'status', 'amount', 'report', 'invoice_id',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'done_at' => 'datetime', 'amount' => 'float'];
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'gray';
    }
}
