<x-layouts.app title="Produits & services">
    <x-page-header title="Produits & services" subtitle="Catalogue du matériel vendu et des prestations facturables.">
        <a href="{{ route('products.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouvel article</a>
    </x-page-header>

    <div class="mb-5 grid gap-4 sm:grid-cols-3">
        <x-stat label="Articles au catalogue" :value="$products->total()" icon="box" />
        <x-stat label="Valeur du stock (prix d'achat)" :value="money($stockValue)" icon="banknotes" tone="emerald" />
        <x-stat label="Articles en stock faible" :value="$lowCount" icon="alert" :tone="$lowCount ? 'red' : 'emerald'" :href="route('products.index', ['low' => 1])" />
    </div>

    <div class="card overflow-hidden">
        <form class="flex flex-wrap gap-3 border-b border-slate-100 p-4">
            <div class="relative min-w-56 flex-1 sm:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="Nom ou référence…" class="input pl-9">
            </div>
            <select name="type" class="input w-auto" onchange="this.form.submit()">
                <option value="">Matériel & services</option>
                <option value="produit" @selected(request('type') === 'produit')>Matériel</option>
                <option value="service" @selected(request('type') === 'service')>Services</option>
            </select>
            <select name="category" class="input w-auto" onchange="this.form.submit()">
                <option value="">Toutes catégories</option>
                @foreach ($categories as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach
            </select>
            @if (request()->hasAny(['q', 'type', 'category', 'low']))<a href="{{ route('products.index') }}" class="btn-ghost">Réinitialiser</a>@endif
        </form>

        @if ($products->isEmpty())
            <x-empty icon="box" title="Catalogue vide" text="Ajoutez vos routeurs, kits Starlink, antennes, câbles… et vos prestations (installation, configuration)." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Article</th><th>Catégorie</th><th class="text-right">Prix d'achat</th><th class="text-right">Prix de vente</th><th class="text-right">Marge</th><th class="text-center">Stock</th></tr></thead>
                    <tbody>
                        @foreach ($products as $p)
                            <tr class="cursor-pointer {{ $p->is_active ? '' : 'opacity-50' }}" onclick="location='{{ route('products.show', $p) }}'">
                                <td>
                                    <p class="font-semibold text-ink">{{ $p->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $p->reference ?: 'Sans réf.' }} · {{ $p->type === 'service' ? 'Service' : 'Matériel' }}</p>
                                </td>
                                <td class="text-slate-600">{{ $p->category ?: '—' }}</td>
                                <td class="num text-right text-slate-600">{{ $p->purchase_price > 0 ? money($p->purchase_price) : '—' }}</td>
                                <td class="num text-right font-semibold text-ink">{{ money($p->sale_price) }}</td>
                                <td class="num text-right {{ $p->margin > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $p->purchase_price > 0 ? money($p->margin) : '—' }}</td>
                                <td class="text-center">
                                    @if ($p->type === 'service')<span class="text-slate-300">—</span>
                                    @else<x-badge :color="$p->stock_qty <= 0 ? 'red' : ($p->is_low_stock ? 'amber' : 'emerald')">{{ $p->stock_qty }} {{ $p->unit }}</x-badge>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $products->links() }}</div>
        @endif
    </div>
</x-layouts.app>
