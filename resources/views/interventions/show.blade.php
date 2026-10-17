<x-layouts.app :title="$intervention->title">
    <x-page-header :title="$intervention->title" :back="route('interventions.index')"
        subtitle="{{ $intervention->type_label }} · <a class='text-primary-600 hover:underline' href='{{ route('clients.show', $intervention->client_id) }}'>{{ e($intervention->client->name) }}</a>">
        <x-badge :color="$intervention->status_color" class="text-xs">{{ $intervention->status_label }}</x-badge>
        @if (! $intervention->invoice_id && $intervention->status === 'terminee')
            <a href="{{ route('invoices.create', ['client_id' => $intervention->client_id, 'intervention_id' => $intervention->id]) }}" class="btn-secondary"><x-icon name="receipt" class="size-4" />Facturer</a>
        @endif
        <a href="{{ route('interventions.edit', $intervention) }}" class="btn-primary"><x-icon name="pencil" class="size-4" />Modifier</a>
        <x-delete-button :action="route('interventions.destroy', $intervention)" />
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-header"><h2 class="card-title">Détails</h2></div>
            <div class="card-body space-y-5 text-sm">
                <div><p class="label">Description</p><p class="whitespace-pre-line text-slate-700">{{ $intervention->description ?: '—' }}</p></div>
                <div><p class="label">Rapport</p><p class="whitespace-pre-line text-slate-700">{{ $intervention->report ?: 'Aucun rapport pour le moment.' }}</p></div>
            </div>
        </div>
        <div class="card">
            <dl class="dl card-body space-y-4">
                <div><dt>Date prévue</dt><dd>{{ $intervention->scheduled_at?->locale('fr')->translatedFormat('l d F Y à H:i') ?? 'Non planifiée' }}</dd></div>
                <div><dt>Terminée le</dt><dd>{{ $intervention->done_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
                <div><dt>Montant</dt><dd class="num">{{ money($intervention->amount) }}</dd></div>
                <div><dt>Client</dt><dd>{{ $intervention->client->name }} · {{ $intervention->client->phone }}</dd></div>
                <div><dt>Facture</dt><dd>@if ($intervention->invoice)<a href="{{ route('invoices.show', $intervention->invoice) }}" class="text-primary-600 hover:underline">{{ $intervention->invoice->number }}</a>@else — @endif</dd></div>
            </dl>
        </div>
    </div>
</x-layouts.app>
