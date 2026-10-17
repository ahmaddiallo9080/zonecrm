<x-layouts.app :title="$intervention->exists ? 'Modifier l\'intervention' : 'Nouvelle intervention'">
    <x-page-header :title="$intervention->exists ? 'Modifier l\'intervention' : 'Nouvelle intervention'"
        :back="$intervention->exists ? route('interventions.show', $intervention) : route('interventions.index')" />

    <form method="POST" action="{{ $intervention->exists ? route('interventions.update', $intervention) : route('interventions.store') }}" class="card max-w-4xl">
        @csrf
        @if ($intervention->exists) @method('PUT') @endif
        <div class="card-body grid gap-5 sm:grid-cols-2">
            <x-select name="client_id" label="Client" :options="$clients" :value="$intervention->client_id" placeholder="— Choisir un client —" required class="sm:col-span-2" />
            <x-select name="type" label="Type" :options="\App\Models\Intervention::TYPES" :value="$intervention->type" required />
            <x-select name="status" label="Statut" :options="\App\Models\Intervention::STATUSES" :value="$intervention->status" required />
            <x-input name="title" label="Titre" :value="$intervention->title" required placeholder="Installation hotspot 3 antennes – Kaloum" class="sm:col-span-2" />
            <x-input name="scheduled_at" type="datetime-local" label="Date & heure prévues" :value="$intervention->scheduled_at?->format('Y-m-d\TH:i')" />
            <x-input name="amount" type="number" step="1" min="0" label="Montant de la prestation ({{ setting('currency') }})" :value="$intervention->amount ? (int) $intervention->amount : null" hint="Pour mémoire — la facturation se fait via une facture." />
            <x-textarea name="description" label="Description / problème signalé" :value="$intervention->description" rows="3" class="sm:col-span-2" />
            <x-textarea name="report" label="Rapport d'intervention" :value="$intervention->report" rows="5" class="sm:col-span-2" placeholder="Travaux réalisés, matériel posé, mesures de signal, recommandations…" />
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ url()->previous() }}" class="btn-secondary">Annuler</a>
            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button>
        </div>
    </form>
</x-layouts.app>
