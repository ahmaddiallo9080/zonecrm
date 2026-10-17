<?php

namespace App\Models;

use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    protected $fillable = ['purchase_id', 'date', 'amount', 'method', 'reference', 'note'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'float'];
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return Options::label(Options::PAYMENT_METHODS, $this->method);
    }
}
