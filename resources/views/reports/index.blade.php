<x-layouts.app title="Trésorerie">
    <x-page-header title="Trésorerie" subtitle="Entrées et sorties d'argent du {{ fdate($from) }} au {{ fdate($to) }}.">
        <button onclick="window.print()" class="btn-secondary"><x-icon name="printer" class="size-4" />Imprimer</button>
    </x-page-header>

    <form class="card mb-5 flex flex-wrap items-end gap-3 p-4 print:hidden">
        <div><label class="label">Du</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input"></div>
        <div><label class="label">Au</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input"></div>
        <button class="btn-secondary">Appliquer</button>
        <div class="ml-auto flex flex-wrap gap-1">
            @foreach (['Aujourd\'hui' => [today(), today()], 'Ce mois' => [now()->startOfMonth(), now()->endOfMonth()], 'Mois dernier' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()], 'Cette année' => [now()->startOfYear(), now()->endOfYear()]] as $lbl => [$a, $b])
                <a href="{{ route('reports.index', ['from' => $a->format('Y-m-d'), 'to' => $b->format('Y-m-d')]) }}" class="btn-ghost btn-sm">{{ $lbl }}</a>
            @endforeach
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Encaissements" :value="money($totals['in'])" icon="banknotes" tone="emerald" :hint="'Facturé : '.money($totals['invoiced'])" />
        <x-stat label="Paiements fournisseurs" :value="money($totals['out_suppliers'])" icon="truck" tone="violet" :hint="'Achats : '.money($totals['purchased'])" />
        <x-stat label="Dépenses" :value="money($totals['out_expenses'])" icon="wallet" tone="amber" />
        <x-stat label="Solde de la période" :value="money($totals['net'])" icon="scale" :tone="$totals['net'] >= 0 ? 'primary' : 'red'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="card h-fit">
            <div class="card-header"><h2 class="card-title">Par mode de paiement</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse ($byMethod as $m => $v)
                    <div class="px-5 py-3.5">
                        <p class="text-sm font-semibold text-ink">{{ \App\Support\Options::PAYMENT_METHODS[$m] }}</p>
                        <div class="mt-1 flex flex-wrap justify-between gap-x-3 gap-y-0.5 text-xs">
                            <span class="num text-emerald-600">+ {{ money($v['in']) }}</span>
                            <span class="num text-red-600">− {{ money($v['out']) }}</span>
                            <span class="num font-bold text-ink">= {{ money($v['in'] - $v['out']) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Aucun mouvement.</p>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden lg:col-span-2">
            <div class="card-header"><h2 class="card-title">Journal des mouvements ({{ $journal->count() }})</h2></div>
            @if ($journal->isEmpty())
                <x-empty icon="chart" title="Aucun mouvement sur la période" />
            @else
                <div class="max-h-[640px] overflow-auto">
                    <table class="table">
                        <thead class="sticky top-0"><tr><th>Date</th><th>Libellé</th><th>Mode</th><th class="text-right">Entrée</th><th class="text-right">Sortie</th></tr></thead>
                        <tbody>
                            @foreach ($journal as $row)
                                <tr class="cursor-pointer" onclick="location='{{ $row['url'] }}'">
                                    <td class="whitespace-nowrap text-slate-600">{{ fdate($row['date']) }}</td>
                                    <td class="max-w-56 truncate text-ink" title="{{ $row['label'] }}">{{ $row['label'] }}</td>
                                    <td class="text-xs text-slate-500">{{ \App\Support\Options::label(\App\Support\Options::PAYMENT_METHODS, $row['method']) }}</td>
                                    <td class="num text-right font-semibold text-emerald-600">{{ $row['in'] ? money($row['in']) : '' }}</td>
                                    <td class="num text-right font-semibold text-red-600">{{ $row['out'] ? money($row['out']) : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
