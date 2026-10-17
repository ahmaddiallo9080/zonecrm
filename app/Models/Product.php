<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type', 'reference', 'name', 'category', 'unit', 'purchase_price', 'sale_price',
        'stock_qty', 'alert_qty', 'supplier_id', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isService(): bool
    {
        return $this->type === 'service';
    }

    public function getIsLowStockAttribute(): bool
    {
        return ! $this->isService() && $this->stock_qty <= $this->alert_qty;
    }

    public function getMarginAttribute(): float
    {
        return (float) $this->sale_price - (float) $this->purchase_price;
    }

    public function scopeLowStock(Builder $q): Builder
    {
        return $q->where('type', 'produit')->whereColumn('stock_qty', '<=', 'alert_qty');
    }

    /** Ajuste le stock (positif = entrée, négatif = sortie). Ignoré pour les services. */
    public function moveStock(float $qty): void
    {
        if ($this->isService()) {
            return;
        }
        $this->increment('stock_qty', (int) round($qty));
    }
}
