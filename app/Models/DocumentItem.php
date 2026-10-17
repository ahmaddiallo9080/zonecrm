<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentItem extends Model
{
    protected $fillable = ['product_id', 'description', 'quantity', 'unit_price', 'total', 'position'];

    protected function casts(): array
    {
        return ['quantity' => 'float', 'unit_price' => 'float', 'total' => 'float'];
    }

    public function document()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
