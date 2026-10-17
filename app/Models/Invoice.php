<?php

namespace App\Models;

use App\Models\Concerns\HasLineItems;
use App\Models\Concerns\HasNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasLineItems, HasNumber;

    public const NUMBER_PREFIX = 'FAC';

    public const TYPES = [
        'vente' => 'Vente de matériel',
        'prestation' => 'Prestation de service',
        'mixte' => 'Matériel + prestation',
    ];

    protected $fillable = [
        'number', 'client_id', 'quote_id', 'type', 'date', 'due_date', 'subject',
        'discount', 'tax_rate', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'due_date' => 'date',
            'is_cancelled' => 'boolean',
            'subtotal' => 'float', 'discount' => 'float', 'tax_rate' => 'float',
            'tax_amount' => 'float', 'total' => 'float', 'amount_paid' => 'float',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class)->latest('date');
    }

    public function getBalanceAttribute(): float
    {
        return $this->is_cancelled ? 0 : max(0, round($this->total - $this->amount_paid, 2));
    }

    public function getStatusAttribute(): string
    {
        if ($this->is_cancelled) {
            return 'annulee';
        }
        if ($this->total > 0 && $this->amount_paid >= $this->total) {
            return 'payee';
        }
        if ($this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday()) {
            return 'en_retard';
        }

        return $this->amount_paid > 0 ? 'partielle' : 'impayee';
    }

    public const STATUSES = [
        'impayee' => ['Impayée', 'amber'],
        'partielle' => ['Partiellement payée', 'blue'],
        'payee' => ['Payée', 'emerald'],
        'en_retard' => ['En retard', 'red'],
        'annulee' => ['Annulée', 'gray'],
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0];
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status][1];
    }

    public function refreshPaid(): void
    {
        $this->amount_paid = (float) $this->payments()->reorder()->sum('amount');
        $this->save();
    }

    public function scopeUnpaid(Builder $q): Builder
    {
        return $q->where('is_cancelled', false)->whereColumn('amount_paid', '<', 'total');
    }

    /** Sortie / retour de stock pour les produits de la facture. */
    public function applyStock(int $direction = -1): void
    {
        foreach ($this->items()->with('product')->get() as $item) {
            $item->product?->moveStock($direction * $item->quantity);
        }
    }
}
