<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    // clé => [libellé, couleur]
    public const STATUSES = [
        'prospect' => ['Prospect', 'gray'],
        'en_installation' => ['En installation', 'amber'],
        'actif' => ['Actif', 'emerald'],
        'maintenance' => ['Sous maintenance', 'blue'],
        'suspendu' => ['Suspendu', 'orange'],
        'inactif' => ['Inactif', 'red'],
    ];

    protected $fillable = [
        'code', 'type', 'name', 'company', 'phone', 'phone2', 'email',
        'address', 'city', 'location', 'status', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->code)) {
                $last = static::withTrashed()->max('id') ?? 0;
                $client->code = 'CLI-'.str_pad((string) ($last + 1), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function accesses()
    {
        return $this->hasMany(ClientAccess::class)->orderBy('type');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class)->latest('date');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class)->latest('date');
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class)->latest('scheduled_at');
    }

    public function payments()
    {
        return $this->hasManyThrough(InvoicePayment::class, Invoice::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company ? "{$this->name} ({$this->company})" : $this->name;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'gray';
    }

    /** Montant total dû par le client (factures non annulées). */
    public function getBalanceAttribute(): float
    {
        return (float) $this->invoices()->reorder()->where('is_cancelled', false)
            ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')->value('due');
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (! $term) {
            return $q;
        }

        return $q->where(fn ($w) => $w->where('name', 'like', "%$term%")
            ->orWhere('company', 'like', "%$term%")
            ->orWhere('phone', 'like', "%$term%")
            ->orWhere('code', 'like', "%$term%")
            ->orWhere('city', 'like', "%$term%"));
    }
}
