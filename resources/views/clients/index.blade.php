<x-layouts.app title="Clients">
    <x-page-header title="Clients" subtitle="{{ $clients->total() }} client(s) — accès, interventions et suivi financier.">
        <a href="{{ route('clients.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouveau client</a>
    </x-page-header>

    {{-- Filtres par statut --}}
    <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ route('clients.index', ['q' => request('q')]) }}"
           class="btn-sm btn {{ ! request('status') ? 'bg-ink text-white' : 'bg-white ring-1 ring-slate-200 text-slate-600' }}">
            Tous <span class="opacity-60">{{ $counts->sum() }}</span>
        </a>
        @foreach (\App\Models\Client::STATUSES as $key => [$label, $color])
            <a href="{{ route('clients.index', ['status' => $key, 'q' => request('q')]) }}"
               class="btn-sm btn {{ request('status') === $key ? 'bg-ink text-white' : 'bg-white ring-1 ring-slate-200 text-slate-600' }}">
                {{ $label }} <span class="opacity-60">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="border-b border-slate-100 p-4">
            <form class="relative max-w-sm">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="Nom, téléphone, code, ville…" class="input pl-9">
            </form>
        </div>
        @if ($clients->isEmpty())
            <x-empty icon="users" title="Aucun client" text="Ajoutez votre premier client pour suivre ses installations et ses accès.">
                <a href="{{ route('clients.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Ajouter un client</a>
            </x-empty>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Client</th><th>Contact</th><th>Ville</th><th>Statut</th><th class="text-center">Accès</th><th class="text-right">Dette</th></tr></thead>
                    <tbody>
                        @foreach ($clients as $c)
                            <tr class="cursor-pointer" onclick="location='{{ route('clients.show', $c) }}'">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-xs font-bold text-primary-700">{{ strtoupper(mb_substr($c->name, 0, 2)) }}</div>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-ink">{{ $c->name }}</p>
                                            <p class="truncate text-xs text-slate-400">{{ $c->code }}{{ $c->company ? ' · '.$c->company : '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-slate-600">{{ $c->phone ?: '—' }}</td>
                                <td class="text-slate-600">{{ $c->city ?: '—' }}</td>
                                <td><x-badge :color="$c->status_color">{{ $c->status_label }}</x-badge></td>
                                <td class="text-center">
                                    @if ($c->accesses_count)<span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500"><x-icon name="key" class="size-3.5" />{{ $c->accesses_count }}</span>@else<span class="text-slate-300">—</span>@endif
                                </td>
                                <td class="num text-right font-semibold {{ $c->due > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $c->due > 0 ? money($c->due) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $clients->links() }}</div>
        @endif
    </div>
</x-layouts.app>
