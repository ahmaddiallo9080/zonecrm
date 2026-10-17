<x-layouts.app :title="$purchase->exists ? 'Modifier '.$purchase->number : 'Nouvel achat'">
    <x-page-header :title="$purchase->exists ? 'Modifier l\'achat '.$purchase->number : 'Nouvel achat fournisseur'"
        :back="$purchase->exists ? route('purchases.show', $purchase) : route('purchases.index')"
        subtitle="Les quantités sont ajoutées au stock et le prix d'achat des articles est mis à jour." />

    <form method="POST" action="{{ $purchase->exists ? route('purchases.update', $purchase) : route('purchases.store') }}" class="space-y-6">
        @csrf
        @if ($purchase->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-select name="supplier_id" label="Fournisseur" :options="$suppliers" :value="$purchase->supplier_id" placeholder="— Choisir —" required class="sm:col-span-2" />
                <x-input name="supplier_ref" label="N° facture fournisseur" :value="$purchase->supplier_ref" class="sm:col-span-2" />
                <x-input name="date" type="date" label="Date d'achat" :value="$purchase->date?->format('Y-m-d')" required />
                <x-input name="due_date" type="date" label="À payer avant le" :value="$purchase->due_date?->format('Y-m-d')" />
            </div>
            <p class="px-5 pb-4 text-xs text-slate-400">Nouveau fournisseur ? <a href="{{ route('suppliers.create') }}" class="font-semibold text-primary-600 hover:underline">Le créer</a> · Nouvel article ? <a href="{{ route('products.create') }}" class="font-semibold text-primary-600 hover:underline">L'ajouter au catalogue</a></p>
        </div>

        <div class="card" x-data="lineEditor({ items: @js(old('items', $items)), products: @js($products), priceField: 'unit_cost', productPrice: 'purchase_price' })">
            <div class="card-header">
                <h2 class="card-title">Articles achetés</h2>
                <button type="button" class="btn-secondary btn-sm" @click="add()"><x-icon name="plus" class="size-3.5" />Ajouter</button>
            </div>
            @error('items')<p class="error px-5 pt-3">{{ $message }}</p>@enderror
            <div class="overflow-x-auto">
                <table class="table min-w-[680px]">
                    <thead><tr><th class="w-60">Article du catalogue</th><th>Désignation</th><th class="w-24">Qté</th><th class="w-36">Coût unitaire ({{ setting('currency') }})</th><th class="w-36 text-right">Total</th><th class="w-10"></th></tr></thead>
                    <tbody>
                        <template x-for="(it, i) in items" :key="it._k">
                            <tr class="hover:!bg-transparent">
                                <td>
                                    <select :name="`items[${i}][product_id]`" x-model="it.product_id" @change="pick(i)" class="input text-xs">
                                        <option value="">— Hors stock (frais…) —</option>
                                        <template x-for="p in products" :key="p.id"><option :value="p.id" x-text="p.name" :selected="String(p.id) === String(it.product_id)"></option></template>
                                    </select>
                                </td>
                                <td><input :name="`items[${i}][description]`" x-model="it.description" class="input" required></td>
                                <td><input :name="`items[${i}][quantity]`" x-model="it.quantity" type="number" step="1" min="1" class="input num" required></td>
                                <td><input :name="`items[${i}][unit_cost]`" x-model="it.unit_cost" type="number" step="1" min="0" class="input num" required></td>
                                <td class="num text-right font-semibold text-ink" x-text="money(lineTotal(it))"></td>
                                <td><button type="button" @click="remove(i)" class="btn-ghost hover:!text-red-600"><x-icon name="x" class="size-4" /></button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-slate-100 p-5">
                <p class="text-sm"><span class="mr-3 font-bold text-ink">Total</span><span class="num text-xl font-extrabold text-primary-700" x-text="money(subtotal)"></span></p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card {{ $purchase->exists ? 'lg:col-span-3' : 'lg:col-span-2' }}"><div class="card-body"><x-textarea name="notes" label="Notes" :value="$purchase->notes" /></div></div>
            @unless ($purchase->exists)
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Paiement effectué maintenant</h2></div>
                    <div class="card-body space-y-4">
                        <x-input name="paid_now" type="number" min="0" step="1" label="Montant payé" hint="Laissez vide pour un achat à crédit." />
                        <x-select name="paid_method" label="Mode" :options="\App\Support\Options::PAYMENT_METHODS" value="especes" />
                    </div>
                </div>
            @endunless
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ $purchase->exists ? route('purchases.show', $purchase) : route('purchases.index') }}" class="btn-secondary">Annuler</a>
            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer l'achat</button>
        </div>
    </form>
</x-layouts.app>
