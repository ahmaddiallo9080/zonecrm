<x-layouts.app title="Tableau de bord">
    <x-page-header title="Bonjour, {{ explode(' ', auth()->user()->name)[0] }} 👋"
        subtitle="Voici l'activité de {{ now()->locale('fr')->translatedFormat('F Y') }}." />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat label="Encaissé ce mois" :value="money($stats['income'])" icon="banknotes" tone="emerald"
            :hint="'Facturé : '.money($stats['sales'])" :href="route('reports.index')" />
        <x-stat label="Sorties ce mois" :value="money($stats['outflow'])" icon="wallet" tone="amber"
            hint="Dépenses + paiements fournisseurs" :href="route('expenses.index')" />
        <x-stat label="Résultat de trésorerie" :value="money($stats['net'])" icon="chart" :tone="$stats['net'] >= 0 ? 'primary' : 'red'"
            hint="Encaissé − sorties" :href="route('reports.index')" />
        <x-stat label="Clients actifs" :value="$stats['clients_active'].' / '.$stats['clients_total']" icon="users" tone="violet"
            :hint="$stats['quotes_pending'].' devis en attente'" :href="route('clients.index')" />
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('debts.index') }}" class="card flex items-center justify-between p-5 transition hover:ring-primary-300">
            <div>
                <p class="text-xs font-semibold text-slate-500">Les clients me doivent</p>
                <p class="num mt-1 text-2xl font-extrabold text-emerald-600">{{ money($stats['receivables']) }}</p>
            </div>
            <x-icon name="arrow-right" class="size-5 text-slate-300" />
        </a>
        <a href="{{ route('debts.index', ['tab' => 'fournisseurs']) }}" class="card flex items-center justify-between p-5 transition hover:ring-primary-300">
            <div>
                <p class="text-xs font-semibold text-slate-500">Je dois aux fournisseurs</p>
                <p class="num mt-1 text-2xl font-extrabold text-red-600">{{ money($stats['payables']) }}</p>
            </div>
            <x-icon name="arrow-right" class="size-5 text-slate-300" />
        </a>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <h2 class="card-title">Encaissements vs sorties — 6 derniers mois</h2>
            </div>
            <div class="card-body"><div class="h-72"><canvas id="cashChart"></canvas></div></div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Interventions à venir</h2>
                <a href="{{ route('interventions.index') }}" class="text-xs font-semibold text-primary-600 hover:underline">Tout voir</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($upcoming as $it)
                    <li>
                        <a href="{{ route('interventions.show', $it) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50">
                            <div class="flex w-11 shrink-0 flex-col items-center rounded-lg bg-primary-50 py-1 text-primary-700">
                                <span class="text-[10px] font-bold uppercase">{{ $it->scheduled_at?->locale('fr')->translatedFormat('M') ?? '—' }}</span>
                                <span class="text-base leading-none font-extrabold">{{ $it->scheduled_at?->format('d') ?? '?' }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink">{{ $it->title }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $it->client->name }} · {{ $it->type_label }} {{ $it->scheduled_at?->format('H:i') }}</p>
                            </div>
                            <x-badge :color="$it->status_color">{{ $it->status_label }}</x-badge>
                        </a>
                    </li>
                @empty
                    <li><x-empty icon="calendar" title="Rien de planifié" text="Ajoutez une installation ou un dépannage." /></li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="card">
            <div class="card-header"><h2 class="card-title flex items-center gap-2"><x-icon name="alert" class="size-4 text-red-500" />Factures en retard</h2></div>
            <ul class="divide-y divide-slate-100">
                @forelse ($overdue as $inv)
                    <li><a href="{{ route('invoices.show', $inv) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                        <div><p class="text-sm font-semibold text-ink">{{ $inv->client->name }}</p><p class="text-xs text-slate-500">{{ $inv->number }} · échue le {{ fdate($inv->due_date) }}</p></div>
                        <span class="num text-sm font-bold text-red-600">{{ money($inv->balance) }}</span>
                    </a></li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-400">Aucune facture en retard 🎉</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title flex items-center gap-2"><x-icon name="box" class="size-4 text-amber-500" />Stock faible</h2></div>
            <ul class="divide-y divide-slate-100">
                @forelse ($lowStock as $p)
                    <li><a href="{{ route('products.show', $p) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                        <div class="min-w-0"><p class="truncate text-sm font-semibold text-ink">{{ $p->name }}</p><p class="text-xs text-slate-500">Seuil : {{ $p->alert_qty }}</p></div>
                        <x-badge :color="$p->stock_qty <= 0 ? 'red' : 'amber'">{{ $p->stock_qty }} {{ $p->unit }}</x-badge>
                    </a></li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-400">Stock suffisant partout.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title flex items-center gap-2"><x-icon name="key" class="size-4 text-primary-500" />Renouvellements proches</h2></div>
            <ul class="divide-y divide-slate-100">
                @forelse ($renewals as $acc)
                    <li><a href="{{ route('clients.show', $acc->client_id) }}#acces" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50">
                        <div class="min-w-0"><p class="truncate text-sm font-semibold text-ink">{{ $acc->client?->name }}</p><p class="truncate text-xs text-slate-500">{{ $acc->type_label }} · {{ $acc->label }}</p></div>
                        <x-badge :color="$acc->renewal_date->isPast() ? 'red' : 'amber'">{{ fdate($acc->renewal_date) }}</x-badge>
                    </a></li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-400">Aucun abonnement à renouveler sous 15 jours.</li>
                @endforelse
            </ul>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        const css = getComputedStyle(document.documentElement);
        const ctx = document.getElementById('cashChart');
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chart['labels']),
                datasets: [
                    { label: 'Encaissements', data: @json($chart['income']), backgroundColor: '#3d63dd', borderRadius: 6, maxBarThickness: 28 },
                    { label: 'Sorties', data: @json($chart['outflow']), backgroundColor: '#c4d1f7', borderRadius: 6, maxBarThickness: 28 },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { family: getComputedStyle(document.body).fontFamily } } },
                    tooltip: { callbacks: { label: c => `${c.dataset.label} : ${new Intl.NumberFormat('fr-FR').format(c.raw)} {{ setting('currency') }}` } },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#eef1f6' }, border: { display: false }, ticks: { callback: v => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v) + ' {{ setting('currency') }}' } },
                },
            },
        });
    </script>
    @endpush
</x-layouts.app>
