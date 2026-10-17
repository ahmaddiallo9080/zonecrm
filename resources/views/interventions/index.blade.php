<x-layouts.app title="Interventions">
    <x-page-header title="Interventions" subtitle="Installations, dépannages, maintenances et configurations.">
        <a href="{{ route('interventions.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouvelle intervention</a>
    </x-page-header>

    <div class="card overflow-hidden">
        <form class="flex flex-wrap gap-3 border-b border-slate-100 p-4">
            <div class="relative min-w-60 flex-1 sm:max-w-sm">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="Titre ou client…" class="input pl-9">
            </div>
            <select name="type" class="input w-auto" onchange="this.form.submit()">
                <option value="">Tous les types</option>
                @foreach (\App\Models\Intervention::TYPES as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach
            </select>
            <select name="status" class="input w-auto" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                @foreach (\App\Models\Intervention::STATUSES as $k => [$l])<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach
            </select>
        </form>

        @if ($interventions->isEmpty())
            <x-empty icon="wrench" title="Aucune intervention" text="Planifiez votre prochaine installation ou un dépannage." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Date</th><th>Intervention</th><th>Client</th><th>Type</th><th>Statut</th><th class="text-right">Montant</th></tr></thead>
                    <tbody>
                        @foreach ($interventions as $it)
                            <tr class="cursor-pointer" onclick="location='{{ route('interventions.show', $it) }}'">
                                <td class="whitespace-nowrap text-slate-600">{{ $it->scheduled_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="font-semibold text-ink">{{ $it->title }}</td>
                                <td class="text-slate-600">{{ $it->client->name }}</td>
                                <td class="text-slate-600">{{ $it->type_label }}</td>
                                <td><x-badge :color="$it->status_color">{{ $it->status_label }}</x-badge></td>
                                <td class="num text-right font-semibold text-ink">{{ $it->amount ? money($it->amount) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $interventions->links() }}</div>
        @endif
    </div>
</x-layouts.app>
