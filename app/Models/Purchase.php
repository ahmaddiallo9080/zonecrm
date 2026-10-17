<?php

namespace App\Models;

use App\Models\Concerns\HasNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasNumber;

    public const NUMBER_PREFIX = 'ACH';

    protected $fillable = ['number', 'supplier_id', 'supplier_ref', 'date', 'due_date', 'notes'];

    protected function casts(): array
    {
        return ['date' => 'date', 'due_date' => 'date', 'total' => 'float', 'amount_paid' => 'float'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class)->latest('date');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, round($this->total - $this->amount_paid, 2));
    }

    public function getStatusAttribute(): string
    {
        if ($this->total > 0 && $this->amount_paid >= $this->total) {
            return 'payee';
        }
        if ($this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday()) {
            return 'en_retard';
        }

        return $this->amount_paid > 0 ? 'partielle' : 'impayee';
    }

    public function getStatusLabelAttribute(): string
    {
        return Invoice::STATUSES[$this->status][0];
    }

    public function getStatusColorAttribute(): string
    {
        return Invoice::STATUSES[$this->status][1];
    }

    public function refreshPaid(): void
    {
        $this->amount_paid = (float) $this->payments()->reorder()->sum('amount');
        $this->save();
    }

    public function scopeUnpaid(Builder $q): Builder
    {
        return $q->whereColumn('amount_paid', '<', 'total');
    }

    public function syncItems(array $lines): void
    {
        // Annule l'entrée en stock précédente
        foreach ($this->items()->with('product')->get() as $old) {
            $old->product?->moveStock(-$old->quantity);
        }
        $this->items()->delete();

        $total = 0;
        foreach ($lines as $line) {
            $qty = (float) $line['quantity'];
            $cost = (float) $line['unit_cost'];
            $item = $this->items()->create([
                'product_id' => $line['product_id'] ?: null,
                'description' => $line['description'],
                'quantity' => $qty,
                'unit_cost' => $cost,
                'total' => round($qty * $cost, 2),
            ]);
            $total += $item->total;

            if ($item->product) {
                $item->product->moveStock($qty);
                // Met à jour le dernier prix d'achat
                $item->product->update(['purchase_price' => $cost, 'supplier_id' => $this->supplier_id]);
            }
        }

        $this->forceFill(['total' => $total])->save();
    }
}
