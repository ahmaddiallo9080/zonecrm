<x-layouts.app title="Achats fournisseurs">
    <x-page-header title="Achats fournisseurs" subtitle="Chaque achat alimente le stock et la dette fournisseur.">
        <a href="{{ route('purchases.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouvel achat</a>
    </x-page-header>

    <div class="mb-5 grid gap-4 sm:grid-cols-3">
        <x-stat label="Total des achats" :value="money($summary->total)" icon="cart" />
        <x-stat label="Payé aux fournisseurs" :value="money($summary->paid)" icon="banknotes" tone="emerald" />
        <x-stat label="Reste à payer" :value="money($summary->total - $summary->paid)" icon="scale" tone="red" :href="route('debts.index', ['tab' => 'fournisseurs'])" />
    </div>

    <div class="card overflow-hidden">
        <form class="flex flex-wrap gap-3 border-b border-slate-100 p-4">
            <input name="q" value="{{ request('q') }}" placeholder="N° achat ou facture fournisseur…" class="input max-w-xs">
            <select name="supplier_id" class="input w-auto" onchange="this.form.submit()">
                <option value="">Tous les fournisseurs</option>
                @foreach ($suppliers as $id => $name)<option value="{{ $id }}" @selected(request('supplier_id') == $id)>{{ $name }}</option>@endforeach
            </select>
            <select name="status" class="input w-auto" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="unpaid" @selected(request('status') === 'unpaid')>Non soldés</option>
                <option value="paid" @selected(request('status') === 'paid')>Payés</option>
            </select>
        </form>
        @if ($purchases->isEmpty())
            <x-empty icon="cart" title="Aucun achat" text="Enregistrez vos achats de matériel pour suivre le stock et vos dettes fournisseurs." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>N°</th><th>Date</th><th>Fournisseur</th><th>Statut</th><th class="text-right">Total</th><th class="text-right">Reste</th></tr></thead>
                    <tbody>
                        @foreach ($purchases as $p)
                            <tr class="cursor-pointer" onclick="location='{{ route('purchases.show', $p) }}'">
                                <td class="font-semibold text-primary-700">{{ $p->number }}<span class="block text-xs font-normal text-slate-400">{{ $p->supplier_ref }}</span></td>
                                <td class="text-slate-600">{{ fdate($p->date) }}</td>
                                <td class="font-medium text-ink">{{ $p->supplier->name }}</td>
                                <td><x-badge :color="$p->status_color">{{ $p->status_label }}</x-badge></td>
                                <td class="num text-right font-bold text-ink">{{ money($p->total) }}</td>
                                <td class="num text-right font-semibold {{ $p->balance > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $p->balance > 0 ? money($p->balance) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $purchases->links() }}</div>
        @endif
    </div>
</x-layouts.app>
