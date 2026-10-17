<?php

namespace App\Models;

use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = ['invoice_id', 'date', 'amount', 'method', 'reference', 'note'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'float'];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return Options::label(Options::PAYMENT_METHODS, $this->method);
    }
}
