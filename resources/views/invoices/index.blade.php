<x-layouts.app title="Factures">
    <x-page-header title="Factures" subtitle="Ventes de matériel et prestations de service.">
        <a href="{{ route('invoices.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouvelle facture</a>
    </x-page-header>

    <div class="mb-5 grid gap-4 sm:grid-cols-3">
        <x-stat label="Total facturé" :value="money($summary->total)" icon="receipt" />
        <x-stat label="Total encaissé" :value="money($summary->paid)" icon="banknotes" tone="emerald" />
        <x-stat label="Reste à encaisser" :value="money($summary->total - $summary->paid)" icon="scale" tone="red" :href="route('debts.index')" />
    </div>

    <div class="card overflow-hidden">
        <form class="flex flex-wrap items-end gap-3 border-b border-slate-100 p-4">
            <div class="relative min-w-56 flex-1 sm:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="N°, objet ou client…" class="input pl-9">
            </div>
            <select name="status" class="input w-auto">
                <option value="">Tous les statuts</option>
                @foreach (\App\Models\Invoice::STATUSES as $k => [$l])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
            </select>
            <select name="type" class="input w-auto">
                <option value="">Tous les types</option>
                @foreach (\App\Models\Invoice::TYPES as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="input w-auto" title="Du">
            <input type="date" name="to" value="{{ request('to') }}" class="input w-auto" title="Au">
            <button class="btn-secondary">Filtrer</button>
            @if (request()->hasAny(['q', 'status', 'type', 'from', 'to']))<a href="{{ route('invoices.index') }}" class="btn-ghost">Réinitialiser</a>@endif
        </form>

        @if ($invoices->isEmpty())
            <x-empty icon="receipt" title="Aucune facture" text="Créez une facture ou convertissez un devis accepté." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>N°</th><th>Date</th><th>Client</th><th>Type</th><th>Statut</th><th class="text-right">Total</th><th class="text-right">Reste</th></tr></thead>
                    <tbody>
                        @foreach ($invoices as $inv)
                            <tr class="cursor-pointer {{ $inv->is_cancelled ? 'opacity-50' : '' }}" onclick="location='{{ route('invoices.show', $inv) }}'">
                                <td class="font-semibold text-primary-700">{{ $inv->number }}</td>
                                <td class="text-slate-600">{{ fdate($inv->date) }}</td>
                                <td class="font-medium text-ink">{{ $inv->client->name }}</td>
                                <td class="text-slate-500">{{ ucfirst($inv->type) }}</td>
                                <td><x-badge :color="$inv->status_color">{{ $inv->status_label }}</x-badge></td>
                                <td class="num text-right font-bold text-ink">{{ money($inv->total) }}</td>
                                <td class="num text-right font-semibold {{ $inv->balance > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $inv->balance > 0 ? money($inv->balance) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $invoices->links() }}</div>
        @endif
    </div>
</x-layouts.app>
