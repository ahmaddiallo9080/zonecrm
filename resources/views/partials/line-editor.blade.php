{{--
    Éditeur de lignes (devis / facture).
    Variables : $items (array), $products, $discount, $taxRate
--}}
<div class="card" x-data="lineEditor({ items: @js($items), products: @js($products), discount: {{ (float) $discount }}, taxRate: {{ (float) $taxRate }} })">
    <div class="card-header">
        <h2 class="card-title">Lignes</h2>
        <button type="button" class="btn-secondary btn-sm" @click="add()"><x-icon name="plus" class="size-3.5" />Ajouter une ligne</button>
    </div>

    @error('items')<p class="error px-5 pt-3">{{ $message }}</p>@enderror

    <div class="overflow-x-auto">
        <table class="table min-w-[720px]">
            <thead><tr><th class="w-56">Produit / service</th><th>Désignation</th><th class="w-24">Qté</th><th class="w-36">Prix unitaire ({{ setting('currency') }})</th><th class="w-36 text-right">Total</th><th class="w-10"></th></tr></thead>
            <tbody>
                <template x-for="(it, i) in items" :key="it._k">
                    <tr class="hover:!bg-transparent">
                        <td>
                            <select :name="`items[${i}][product_id]`" x-model="it.product_id" @change="pick(i)" class="input text-xs">
                                <option value="">— Saisie libre —</option>
                                <optgroup label="Matériel">
                                    <template x-for="p in products.filter(p => p.type === 'produit')" :key="p.id">
                                        <option :value="p.id" x-text="`${p.name} (stock ${p.stock_qty})`" :selected="String(p.id) === String(it.product_id)"></option>
                                    </template>
                                </optgroup>
                                <optgroup label="Services">
                                    <template x-for="p in products.filter(p => p.type === 'service')" :key="p.id">
                                        <option :value="p.id" x-text="p.name" :selected="String(p.id) === String(it.product_id)"></option>
                                    </template>
                                </optgroup>
                            </select>
                        </td>
                        <td><input :name="`items[${i}][description]`" x-model="it.description" class="input" placeholder="Désignation" required></td>
                        <td><input :name="`items[${i}][quantity]`" x-model="it.quantity" type="number" step="0.01" min="0.01" class="input num" required></td>
                        <td><input :name="`items[${i}][unit_price]`" x-model="it.unit_price" type="number" step="1" min="0" class="input num" required></td>
                        <td class="num text-right font-semibold text-ink" x-text="money(lineTotal(it))"></td>
                        <td><button type="button" @click="remove(i)" class="btn-ghost hover:!text-red-600"><x-icon name="x" class="size-4" /></button></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="flex justify-end border-t border-slate-100 p-5">
        <div class="w-full max-w-xs space-y-3 text-sm">
            <div class="flex items-center justify-between"><span class="text-slate-500">Sous-total</span><span class="num font-semibold text-ink" x-text="money(subtotal)"></span></div>
            <div class="flex items-center justify-between gap-3"><label class="text-slate-500">Remise ({{ setting('currency') }})</label><input name="discount" x-model.number="discount" type="number" min="0" step="1" class="input num w-36 text-right"></div>
            <div class="flex items-center justify-between gap-3"><label class="text-slate-500">TVA (%)</label><input name="tax_rate" x-model.number="taxRate" type="number" min="0" max="100" step="0.01" class="input num w-36 text-right"></div>
            <div class="flex items-center justify-between" x-show="taxAmount > 0"><span class="text-slate-500">Montant TVA</span><span class="num font-semibold text-ink" x-text="money(taxAmount)"></span></div>
            <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-3">
                <span class="font-bold text-ink">Total</span>
                <span class="num text-xl font-extrabold text-primary-700" x-text="money(total)"></span>
            </div>
        </div>
    </div>
</div>
