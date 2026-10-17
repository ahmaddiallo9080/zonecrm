<x-layouts.app title="Dépenses">
    <x-page-header title="Dépenses" subtitle="Charges de fonctionnement : transport, carburant, outillage, crédit, loyer…">
        <a href="{{ route('expenses.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouvelle dépense</a>
    </x-page-header>

    <form class="card mb-5 flex flex-wrap items-end gap-3 p-4">
        <div><label class="label">Du</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input"></div>
        <div><label class="label">Au</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input"></div>
        <div><label class="label">Catégorie</label>
            <select name="category" class="input">
                <option value="">Toutes</option>
                @foreach (\App\Support\Options::EXPENSE_CATEGORIES as $k => $l)<option value="{{ $k }}" @selected(request('category') === $k)>{{ $l }}</option>@endforeach
            </select>
        </div>
        <button class="btn-secondary">Appliquer</button>
        <div class="ml-auto flex gap-1">
            @foreach (['Ce mois' => [now()->startOfMonth(), now()->endOfMonth()], 'Mois dernier' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()], 'Cette année' => [now()->startOfYear(), now()->endOfYear()]] as $lbl => [$a, $b])
                <a href="{{ route('expenses.index', ['from' => $a->format('Y-m-d'), 'to' => $b->format('Y-m-d')]) }}" class="btn-ghost btn-sm">{{ $lbl }}</a>
            @endforeach
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card overflow-hidden lg:col-span-2">
            @if ($expenses->isEmpty())
                <x-empty icon="wallet" title="Aucune dépense sur la période" />
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Date</th><th>Description</th><th>Catégorie</th><th>Mode</th><th class="text-right">Montant</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($expenses as $e)
                                <tr>
                                    <td class="whitespace-nowrap text-slate-600">{{ fdate($e->date) }}</td>
                                    <td>
                                        <p class="font-medium text-ink">{{ $e->description }}</p>
                                        <p class="text-xs text-slate-400">{{ collect([$e->supplier?->name, $e->client ? 'Chantier : '.$e->client->name : null, $e->reference])->filter()->join(' · ') }}</p>
                                    </td>
                                    <td><x-badge color="gray">{{ $e->category_label }}</x-badge></td>
                                    <td class="text-xs text-slate-500">{{ $e->method_label }}</td>
                                    <td class="num text-right font-bold text-ink">{{ money($e->amount) }}</td>
                                    <td class="whitespace-nowrap text-right">
                                        <a href="{{ route('expenses.edit', $e) }}" class="btn-ghost"><x-icon name="pencil" class="size-4" /></a>
                                        <x-delete-button :action="route('expenses.destroy', $e)" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-4 py-3">{{ $expenses->links() }}</div>
            @endif
        </div>

        <div class="card h-fit">
            <div class="card-header"><h2 class="card-title">Répartition</h2></div>
            <div class="card-body">
                <p class="text-xs font-semibold text-slate-500">Total de la période</p>
                <p class="num mb-5 text-2xl font-extrabold text-ink">{{ money($total) }}</p>
                <div class="space-y-3.5">
                    @forelse ($byCategory as $cat => $sum)
                        <div>
                            <div class="mb-1 flex justify-between text-xs"><span class="font-medium text-slate-600">{{ \App\Support\Options::label(\App\Support\Options::EXPENSE_CATEGORIES, $cat) }}</span><span class="num font-semibold text-ink">{{ money($sum) }}</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-primary-600" style="width: {{ $total > 0 ? round($sum / $total * 100) : 0 }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">—</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
