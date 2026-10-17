<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'description', 'quantity', 'unit_cost', 'total'];

    protected function casts(): array
    {
        return ['quantity' => 'float', 'unit_cost' => 'float', 'total' => 'float'];
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
