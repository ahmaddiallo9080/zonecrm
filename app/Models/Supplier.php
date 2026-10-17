<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'contact_person', 'phone', 'email', 'address', 'country', 'notes'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class)->latest('date');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /** Montant que je dois encore au fournisseur. */
    public function getBalanceAttribute(): float
    {
        return (float) $this->purchases()->reorder()->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')->value('due');
    }
}
