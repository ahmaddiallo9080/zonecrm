<?php

namespace App\Models;

use App\Support\Options;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['date', 'category', 'description', 'amount', 'method', 'reference', 'supplier_id', 'client_id'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'float'];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function getCategoryLabelAttribute(): string
    {
        return Options::label(Options::EXPENSE_CATEGORIES, $this->category);
    }

    public function getMethodLabelAttribute(): string
    {
        return Options::label(Options::PAYMENT_METHODS, $this->method);
    }
}
