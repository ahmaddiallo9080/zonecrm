<x-layouts.app title="Dettes">
    <x-page-header title="Gestion des dettes" subtitle="Ce que vos clients vous doivent et ce que vous devez à vos fournisseurs." />

    <div class="grid gap-4 md:grid-cols-3">
        <div class="card p-5">
            <p class="text-xs font-semibold text-slate-500">Créances clients</p>
            <p class="num mt-1 text-2xl font-extrabold text-emerald-600">{{ money($totalClients) }}</p>
            <p class="text-xs text-slate-400">{{ $clientDebts->count() }} client(s) débiteur(s)</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-slate-500">Dettes fournisseurs</p>
            <p class="num mt-1 text-2xl font-extrabold text-red-600">{{ money($totalSuppliers) }}</p>
            <p class="text-xs text-slate-400">{{ $supplierDebts->count() }} fournisseur(s)</p>
        </div>
        <div class="card p-5">
            <p class="text-xs font-semibold text-slate-500">Position nette</p>
            <p class="num mt-1 text-2xl font-extrabold {{ $totalClients - $totalSuppliers >= 0 ? 'text-primary-700' : 'text-red-600' }}">{{ money($totalClients - $totalSuppliers) }}</p>
            <p class="text-xs text-slate-400">Créances − dettes</p>
        </div>
    </div>

    <div class="card mt-4 p-5">
        <p class="mb-3 text-xs font-semibold text-slate-500">Ancienneté des créances clients (jours depuis la facture)</p>
        @php $agingTotal = max(1, array_sum($aging)); $agingColors = ['0-30' => 'bg-emerald-500', '31-60' => 'bg-amber-400', '61-90' => 'bg-orange-500', '90+' => 'bg-red-600']; @endphp
        <div class="flex h-3 overflow-hidden rounded-full bg-slate-100">
            @foreach ($aging as $k => $v)<div class="{{ $agingColors[$k] }}" style="width: {{ $v / $agingTotal * 100 }}%"></div>@endforeach
        </div>
        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($aging as $k => $v)
                <div class="flex items-center gap-2 text-xs"><span class="size-2.5 rounded-full {{ $agingColors[$k] }}"></span><span class="text-slate-500">{{ $k }} j</span><span class="num ml-auto font-bold text-ink">{{ money($v) }}</span></div>
            @endforeach
        </div>
    </div>

    <div class="mt-6 mb-5 flex gap-6 overflow-x-auto border-b border-slate-200">
        <a href="{{ route('debts.index') }}" class="tab {{ $tab === 'clients' ? 'active' : '' }}">Clients qui me doivent</a>
        <a href="{{ route('debts.index', ['tab' => 'fournisseurs']) }}" class="tab {{ $tab === 'fournisseurs' ? 'active' : '' }}">Fournisseurs à payer</a>
    </div>

    @if ($tab === 'clients')
        <div class="space-y-4">
            @forelse ($clientDebts as $c)
                <div class="card overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex w-full flex-wrap items-center justify-between gap-3 px-5 py-4 text-left hover:bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-full bg-red-50 text-sm font-bold text-red-600">{{ strtoupper(mb_substr($c->name, 0, 2)) }}</div>
                            <div>
                                <p class="font-bold text-ink">{{ $c->name }}</p>
                                <p class="text-xs text-slate-500">{{ $c->invoices->count() }} facture(s) · depuis le {{ fdate($c->oldest) }} · {{ $c->phone }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            @if ($c->overdue > 0)<x-badge color="red">{{ money($c->overdue) }} en retard</x-badge>@endif
                            <span class="num text-lg font-extrabold text-red-600">{{ money($c->due) }}</span>
                            <x-icon name="arrow-right" class="size-4 text-slate-400 transition" ::class="open && 'rotate-90'" />
                        </div>
                    </button>
                    <div x-show="open" x-cloak class="border-t border-slate-100">
                        <table class="table">
                            <thead><tr><th>Facture</th><th>Date</th><th>Échéance</th><th class="text-right">Total</th><th class="text-right">Payé</th><th class="text-right">Reste</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($c->invoices as $inv)
                                    <tr>
                                        <td class="font-semibold text-primary-700">{{ $inv->number }}</td>
                                        <td class="text-slate-600">{{ fdate($inv->date) }}</td>
                                        <td class="{{ $inv->due_date && $inv->due_date->lt(today()) ? 'font-semibold text-red-600' : 'text-slate-600' }}">{{ fdate($inv->due_date) }}</td>
                                        <td class="num text-right">{{ money($inv->total) }}</td>
                                        <td class="num text-right text-emerald-600">{{ money($inv->amount_paid) }}</td>
                                        <td class="num text-right font-bold text-red-600">{{ money($inv->balance) }}</td>
                                        <td class="text-right"><a href="{{ route('invoices.show', $inv) }}" class="btn-primary btn-sm">Encaisser</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="card"><x-empty icon="check" title="Aucune créance" text="Tous vos clients sont à jour. 🎉" /></div>
            @endforelse
        </div>
    @else
        <div class="space-y-4">
            @forelse ($supplierDebts as $s)
                <div class="card overflow-hidden" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex w-full flex-wrap items-center justify-between gap-3 px-5 py-4 text-left hover:bg-slate-50">
                        <div>
                            <p class="font-bold text-ink">{{ $s->name }}</p>
                            <p class="text-xs text-slate-500">{{ $s->purchases->count() }} achat(s) · depuis le {{ fdate($s->oldest) }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            @if ($s->overdue > 0)<x-badge color="red">{{ money($s->overdue) }} en retard</x-badge>@endif
                            <span class="num text-lg font-extrabold text-red-600">{{ money($s->due) }}</span>
                        </div>
                    </button>
                    <div x-show="open" x-cloak class="border-t border-slate-100">
                        <table class="table">
                            <thead><tr><th>Achat</th><th>Date</th><th>À payer avant</th><th class="text-right">Total</th><th class="text-right">Reste</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($s->purchases as $p)
                                    <tr>
                                        <td class="font-semibold text-primary-700">{{ $p->number }}</td>
                                        <td class="text-slate-600">{{ fdate($p->date) }}</td>
                                        <td class="{{ $p->due_date && $p->due_date->lt(today()) ? 'font-semibold text-red-600' : 'text-slate-600' }}">{{ fdate($p->due_date) }}</td>
                                        <td class="num text-right">{{ money($p->total) }}</td>
                                        <td class="num text-right font-bold text-red-600">{{ money($p->balance) }}</td>
                                        <td class="text-right"><a href="{{ route('purchases.show', $p) }}" class="btn-primary btn-sm">Payer</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="card"><x-empty icon="check" title="Aucune dette fournisseur" /></div>
            @endforelse
        </div>
    @endif
</x-layouts.app>
