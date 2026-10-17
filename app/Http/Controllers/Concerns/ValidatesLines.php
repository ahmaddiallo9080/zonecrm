<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use Illuminate\Http\Request;

trait ValidatesLines
{
    protected function validateLines(Request $request, string $priceField = 'unit_price'): array
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn ($i) => filled($i['description'] ?? null) || filled($i['product_id'] ?? null))
                ->values()->all(),
        ]);

        return $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            "items.*.$priceField" => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Ajoutez au moins une ligne.',
        ])['items'];
    }

    /** Produits pour le sélecteur des lignes. */
    protected function productOptions()
    {
        return Product::where('is_active', true)->orderBy('type')->orderBy('name')
            ->get(['id', 'name', 'type', 'sale_price', 'purchase_price', 'stock_qty', 'unit']);
    }
}
