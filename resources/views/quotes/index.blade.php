<x-layouts.app title="Devis">
    <x-page-header title="Devis" subtitle="Propositions commerciales pour vos clients.">
        <a href="{{ route('quotes.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouveau devis</a>
    </x-page-header>

    <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-5">
        @foreach (\App\Models\Quote::STATUSES as $key => [$label, $color])
            <a href="{{ route('quotes.index', ['status' => request('status') === $key ? null : $key]) }}"
               class="card p-4 transition hover:ring-primary-300 {{ request('status') === $key ? '!ring-2 !ring-primary-500' : '' }}">
                <x-badge :color="$color">{{ $label }}</x-badge>
                <p class="num mt-2 text-lg font-extrabold text-ink">{{ $totals[$key]->c ?? 0 }}</p>
                <p class="num text-xs text-slate-500">{{ money($totals[$key]->t ?? 0) }}</p>
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <form class="border-b border-slate-100 p-4">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div class="relative max-w-sm">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="N°, objet ou client…" class="input pl-9">
            </div>
        </form>
        @if ($quotes->isEmpty())
            <x-empty icon="doc" title="Aucun devis" text="Créez un devis pour une installation ou une vente de matériel." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>N°</th><th>Date</th><th>Client</th><th>Objet</th><th>Validité</th><th>Statut</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @foreach ($quotes as $q)
                            <tr class="cursor-pointer" onclick="location='{{ route('quotes.show', $q) }}'">
                                <td class="font-semibold text-primary-700">{{ $q->number }}</td>
                                <td class="text-slate-600">{{ fdate($q->date) }}</td>
                                <td class="font-medium text-ink">{{ $q->client->name }}</td>
                                <td class="max-w-56 truncate text-slate-500">{{ $q->subject ?: '—' }}</td>
                                <td class="{{ $q->isExpired() ? 'font-semibold text-orange-600' : 'text-slate-600' }}">{{ fdate($q->valid_until) }}</td>
                                <td><x-badge :color="$q->status_color">{{ $q->isExpired() ? 'Expiré' : $q->status_label }}</x-badge></td>
                                <td class="num text-right font-bold text-ink">{{ money($q->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $quotes->links() }}</div>
        @endif
    </div>
</x-layouts.app>
