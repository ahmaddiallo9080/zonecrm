<x-layouts.app title="Recherche">
    <x-page-header title="Résultats pour « {{ $q }} »" />

    @if (empty($results))
        <div class="card"><x-empty icon="search" title="Aucun résultat" text="Essayez un nom, un numéro de téléphone, un numéro de facture ou de devis." /></div>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($results as $group => $rows)
                <div class="card">
                    <div class="card-header"><h2 class="card-title">{{ $group }} <span class="text-slate-400">({{ $rows->count() }})</span></h2></div>
                    @foreach ($rows as [$url, $title, $sub, $icon])
                        <a href="{{ $url }}" class="flex items-center gap-3 border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-primary-50 text-primary-600"><x-icon :name="$icon" class="size-4" /></span>
                            <span class="min-w-0"><span class="block truncate text-sm font-semibold text-ink">{{ $title }}</span><span class="block truncate text-xs text-slate-500">{{ $sub }}</span></span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
