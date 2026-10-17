<?php

namespace App\Models\Concerns;

use App\Models\DocumentItem;

/**
 * Lignes + calcul des totaux pour devis et factures.
 */
trait HasLineItems
{
    public function items()
    {
        return $this->morphMany(DocumentItem::class, 'document')->orderBy('position');
    }

    /**
     * Remplace les lignes et recalcule sous-total, remise, TVA et total.
     *
     * @param  array<int, array{product_id?:int|null, description:string, quantity:float, unit_price:float}>  $lines
     */
    public function syncItems(array $lines): void
    {
        $this->items()->delete();

        $subtotal = 0;
        foreach (array_values($lines) as $i => $line) {
            $qty = (float) $line['quantity'];
            $price = (float) $line['unit_price'];
            $total = round($qty * $price, 2);
            $subtotal += $total;

            $this->items()->create([
                'product_id' => $line['product_id'] ?: null,
                'description' => $line['description'],
                'quantity' => $qty,
                'unit_price' => $price,
                'total' => $total,
                'position' => $i,
            ]);
        }

        $discount = min((float) $this->discount, $subtotal);
        $taxable = $subtotal - $discount;
        $tax = round($taxable * ((float) $this->tax_rate) / 100, 2);

        $this->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_amount' => $tax,
            'total' => $taxable + $tax,
        ])->save();
    }
}
