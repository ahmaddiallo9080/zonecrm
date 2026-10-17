<x-layouts.app :title="$product->name">
    <x-page-header :title="$product->name" :back="route('products.index')"
        subtitle="{{ $product->reference ?: 'Sans référence' }} · {{ $product->category ?: 'Sans catégorie' }} · {{ $product->type === 'service' ? 'Service' : 'Matériel' }}">
        <a href="{{ route('products.edit', $product) }}" class="btn-primary"><x-icon name="pencil" class="size-4" />Modifier</a>
        <x-delete-button :action="route('products.destroy', $product)" confirm="Archiver cet article ? Il restera visible dans les anciens documents." />
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat label="Prix de vente" :value="money($product->sale_price)" icon="banknotes" />
        <x-stat label="Prix d'achat" :value="money($product->purchase_price)" icon="cart" tone="violet" />
        <x-stat label="Marge unitaire" :value="money($product->margin)" icon="chart" tone="emerald"
            :hint="$product->sale_price > 0 ? round($product->margin / $product->sale_price * 100).' % du prix de vente' : null" />
        @if ($product->type === 'produit')
            <x-stat label="Stock" :value="$product->stock_qty.' '.$product->unit" icon="box" :tone="$product->is_low_stock ? 'red' : 'emerald'" :hint="'Seuil d\'alerte : '.$product->alert_qty" />
        @endif
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Dernières ventes</h2></div>
            @forelse ($sales as $s)
                <a href="{{ route('invoices.show', $s->document_id) }}" class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50">
                    <div><p class="text-sm font-semibold text-ink">{{ $s->document?->client?->name }}</p><p class="text-xs text-slate-500">{{ $s->document?->number }} · {{ fdate($s->document?->date) }}</p></div>
                    <span class="num text-sm text-slate-600">{{ $s->quantity + 0 }} × {{ money($s->unit_price) }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-400">Pas encore vendu.</p>
            @endforelse
        </div>
        <div class="card">
            <div class="card-header"><h2 class="card-title">Derniers achats</h2></div>
            @forelse ($purchases as $p)
                <a href="{{ route('purchases.show', $p->purchase_id) }}" class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50">
                    <div><p class="text-sm font-semibold text-ink">{{ $p->purchase?->supplier?->name }}</p><p class="text-xs text-slate-500">{{ $p->purchase?->number }} · {{ fdate($p->purchase?->date) }}</p></div>
                    <span class="num text-sm text-slate-600">{{ $p->quantity + 0 }} × {{ money($p->unit_cost) }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-slate-400">Aucun achat enregistré.</p>
            @endforelse
        </div>
    </div>
    @if ($product->description)
        <div class="card mt-6"><div class="card-body text-sm whitespace-pre-line text-slate-600">{{ $product->description }}</div></div>
    @endif
</x-layouts.app>
