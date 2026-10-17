<x-layouts.app :title="$supplier->name">
@php
    $purchased = $supplier->purchases->sum('total');
    $paid = $supplier->purchases->sum('amount_paid');
@endphp
    <x-page-header :title="$supplier->name" :back="route('suppliers.index')"
        subtitle="{{ collect([$supplier->contact_person, $supplier->phone, $supplier->email, $supplier->country])->filter()->join(' · ') ?: 'Aucun contact renseigné' }}">
        <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn-secondary"><x-icon name="cart" class="size-4" />Nouvel achat</a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-primary"><x-icon name="pencil" class="size-4" />Modifier</a>
        <x-delete-button :action="route('suppliers.destroy', $supplier)" confirm="Archiver ce fournisseur ?" />
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat label="Total des achats" :value="money($purchased)" icon="cart" />
        <x-stat label="Déjà payé" :value="money($paid)" icon="banknotes" tone="emerald" />
        <x-stat label="Je dois encore" :value="money($purchased - $paid)" icon="scale" :tone="$purchased - $paid > 0 ? 'red' : 'emerald'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card overflow-hidden lg:col-span-2">
            <div class="card-header"><h2 class="card-title">Achats</h2></div>
            @if ($supplier->purchases->isEmpty())
                <x-empty icon="cart" title="Aucun achat" />
            @else
                <table class="table">
                    <thead><tr><th>N°</th><th>Date</th><th>Statut</th><th class="text-right">Total</th><th class="text-right">Reste</th></tr></thead>
                    <tbody>
                        @foreach ($supplier->purchases as $p)
                            <tr class="cursor-pointer" onclick="location='{{ route('purchases.show', $p) }}'">
                                <td class="font-semibold text-primary-700">{{ $p->number }}<span class="block text-xs font-normal text-slate-400">{{ $p->supplier_ref }}</span></td>
                                <td class="text-slate-600">{{ fdate($p->date) }}</td>
                                <td><x-badge :color="$p->status_color">{{ $p->status_label }}</x-badge></td>
                                <td class="num text-right text-ink">{{ money($p->total) }}</td>
                                <td class="num text-right font-semibold {{ $p->balance > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $p->balance > 0 ? money($p->balance) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="space-y-6">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Articles fournis</h2></div>
                @forelse ($supplier->products as $prod)
                    <a href="{{ route('products.show', $prod) }}" class="flex justify-between border-b border-slate-100 px-5 py-2.5 text-sm last:border-0 hover:bg-slate-50">
                        <span class="truncate text-ink">{{ $prod->name }}</span><span class="num text-slate-500">{{ money($prod->purchase_price) }}</span>
                    </a>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">—</p>
                @endforelse
            </div>
            @if ($supplier->notes || $supplier->address)
                <div class="card card-body text-sm whitespace-pre-line text-slate-600">{{ $supplier->address }}{{ $supplier->address && $supplier->notes ? "\n\n" : '' }}{{ $supplier->notes }}</div>
            @endif
        </div>
    </div>
</x-layouts.app>
