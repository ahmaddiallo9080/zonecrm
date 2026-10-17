<x-layouts.app title="Fournisseurs">
    <x-page-header title="Fournisseurs" subtitle="Vos sources de matériel et ce que vous leur devez.">
        <a href="{{ route('purchases.create') }}" class="btn-secondary"><x-icon name="cart" class="size-4" />Nouvel achat</a>
        <a href="{{ route('suppliers.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" />Nouveau fournisseur</a>
    </x-page-header>

    <div class="card overflow-hidden">
        <form class="border-b border-slate-100 p-4">
            <div class="relative max-w-sm">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}" placeholder="Nom, contact, téléphone…" class="input pl-9">
            </div>
        </form>
        @if ($suppliers->isEmpty())
            <x-empty icon="truck" title="Aucun fournisseur" text="Ajoutez les grossistes et revendeurs chez qui vous achetez votre matériel." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Fournisseur</th><th>Contact</th><th>Pays</th><th class="text-center">Achats</th><th class="text-right">Total acheté</th><th class="text-right">Je dois</th></tr></thead>
                    <tbody>
                        @foreach ($suppliers as $s)
                            @php $due = (float) $s->purchased - (float) $s->paid; @endphp
                            <tr class="cursor-pointer" onclick="location='{{ route('suppliers.show', $s) }}'">
                                <td class="font-semibold text-ink">{{ $s->name }}</td>
                                <td class="text-slate-600">{{ $s->contact_person ?: '—' }}<span class="block text-xs text-slate-400">{{ $s->phone }}</span></td>
                                <td class="text-slate-600">{{ $s->country ?: '—' }}</td>
                                <td class="text-center text-slate-600">{{ $s->purchases_count }}</td>
                                <td class="num text-right text-ink">{{ money($s->purchased ?? 0) }}</td>
                                <td class="num text-right font-semibold {{ $due > 0 ? 'text-red-600' : 'text-slate-300' }}">{{ $due > 0 ? money($due) : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-4 py-3">{{ $suppliers->links() }}</div>
        @endif
    </div>
</x-layouts.app>
