<x-layouts.app :title="$quote->exists ? 'Modifier '.$quote->number : 'Nouveau devis'">
    <x-page-header :title="$quote->exists ? 'Modifier le devis '.$quote->number : 'Nouveau devis'"
        :back="$quote->exists ? route('quotes.show', $quote) : route('quotes.index')" />

    <form method="POST" action="{{ $quote->exists ? route('quotes.update', $quote) : route('quotes.store') }}" class="space-y-6">
        @csrf
        @if ($quote->exists) @method('PUT') @endif

        <div class="card">
            <div class="card-body grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-select name="client_id" label="Client" :options="$clients" :value="$quote->client_id" placeholder="— Choisir —" required class="sm:col-span-2" />
                <x-input name="date" type="date" label="Date" :value="$quote->date?->format('Y-m-d')" required />
                <x-input name="valid_until" type="date" label="Valable jusqu'au" :value="$quote->valid_until?->format('Y-m-d')" />
                <x-input name="subject" label="Objet" :value="$quote->subject" placeholder="Installation Wi-Fi Zone – 4 points d'accès" class="sm:col-span-2 lg:col-span-4" />
            </div>
            <p class="px-5 pb-4 text-xs text-slate-400">Client absent de la liste ? <a href="{{ route('clients.create') }}" class="font-semibold text-primary-600 hover:underline">Créer un client</a></p>
        </div>

        @include('partials.line-editor', ['items' => old('items', $items), 'discount' => old('discount', $quote->discount), 'taxRate' => old('tax_rate', $quote->tax_rate)])

        <div class="card">
            <div class="card-body"><x-textarea name="notes" label="Conditions / notes affichées sur le devis" :value="$quote->notes" rows="3" /></div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
                <a href="{{ $quote->exists ? route('quotes.show', $quote) : route('quotes.index') }}" class="btn-secondary">Annuler</a>
                <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer le devis</button>
            </div>
        </div>
    </form>
</x-layouts.app>
