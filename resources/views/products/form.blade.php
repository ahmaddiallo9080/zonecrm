<x-layouts.app :title="$product->exists ? 'Modifier ' . $product->name : 'Nouvel article'">
    <x-page-header :title="$product->exists ? 'Modifier l\'article' : 'Nouvel article'" :back="$product->exists ? route('products.show', $product) : route('products.index')" />

    <form method="POST" action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}"
        class="card max-w-4xl" x-data="{ type: '{{ old('type', $product->type) }}' }">
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif
        <div class="card-body grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="label">Type d'article</label>
                <div class="grid grid-cols-2 gap-3">
                    @foreach (['produit' => ['Matériel', 'Géré en stock (routeur, kit, câble…)', 'box'], 'service' => ['Service', 'Prestation (installation, configuration…)', 'wrench']] as $val => [$t, $d, $i])
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-4 ring-1 transition"
                            :class="type === '{{ $val }}' ? 'ring-2 ring-primary-500 bg-primary-50/50' :
                                'ring-slate-200'">
                            <input type="radio" name="type" value="{{ $val }}" x-model="type"
                                class="sr-only">
                            <x-icon :name="$i" class="size-5 text-primary-600" />
                            <span><span class="block text-sm font-bold text-ink">{{ $t }}</span><span
                                    class="text-xs text-slate-500">{{ $d }}</span></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <x-input name="name" label="Désignation" :value="$product->name" required class="sm:col-span-2"
                placeholder="MikroTik hAP ax² / Kit Starlink Standard / Installation hotspot…" />
            <x-input name="reference" label="Référence / SKU" :value="$product->reference" />
            <div>
                <label class="label" for="category">Catégorie</label>
                <input id="category" name="category" list="cats" value="{{ old('category', $product->category) }}"
                    class="input">
                <datalist id="cats">
                    @foreach ($categories as $c)
                        <option value="{{ $c }}">
                    @endforeach
                </datalist>
            </div>
            <x-input name="purchase_price" type="number" step="1" min="0"
                label="Prix d'achat ({{ setting('currency') }})" :value="$product->purchase_price ? (int) $product->purchase_price : null" />
            <x-input name="sale_price" type="number" step="1" min="0"
                label="Prix de vente ({{ setting('currency') }})" :value="$product->sale_price !== null ? (int) $product->sale_price : null" required />
            <x-input name="unit" label="Unité" :value="$product->unit" required list="units" />
            <datalist id="units">
                <option value="pièce">
                <option value="mètre">
                <option value="rouleau">
                <option value="kit">
                <option value="forfait">
                <option value="heure">
                <option value="jour">
            </datalist>
            <x-select name="supplier_id" label="Fournisseur habituel" :options="$suppliers" :value="$product->supplier_id"
                placeholder="—" />
            <template x-if="type === 'produit'">
                <div class="grid gap-5 sm:col-span-2 sm:grid-cols-2">
                    <x-input name="stock_qty" type="number" step="1" label="Stock actuel" :value="$product->stock_qty"
                        :hint="$product->exists
                            ? 'Les achats et factures mettent le stock à jour automatiquement.'
                            : 'Stock initial.'" />
                    <x-input name="alert_qty" type="number" step="1" min="0" label="Seuil d'alerte"
                        :value="$product->alert_qty" />
                </div>
            </template>
            <x-textarea name="description" label="Description / caractéristiques" :value="$product->description"
                class="sm:col-span-2" />
            <label class="flex items-center gap-2 text-sm font-medium text-slate-600 sm:col-span-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))
                    class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"> Article actif (proposé
                dans les devis et factures)
            </label>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ $product->exists ? route('products.show', $product) : route('products.index') }}"
                class="btn-secondary">Annuler</a>
            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button>
        </div>
    </form>
</x-layouts.app>
