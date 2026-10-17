<x-layouts.app :title="$invoice->exists ? 'Modifier '.$invoice->number : 'Nouvelle facture'">
    <x-page-header :title="$invoice->exists ? 'Modifier la facture '.$invoice->number : 'Nouvelle facture'"
        :back="$invoice->exists ? route('invoices.show', $invoice) : route('invoices.index')"
        subtitle="Le matériel facturé est automatiquement retiré du stock." />

    <form method="POST" action="{{ $invoice->exists ? route('invoices.update', $invoice) : route('invoices.store') }}" class="space-y-6">
        @csrf
        @if ($invoice->exists) @method('PUT') @endif
        @if (! empty($intervention))<input type="hidden" name="intervention_id" value="{{ $intervention->id }}">@endif

        <div class="card">
            <div class="card-body grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <x-select name="client_id" label="Client" :options="$clients" :value="$invoice->client_id" placeholder="— Choisir —" required class="sm:col-span-2" />
                <x-select name="type" label="Type de facture" :options="\App\Models\Invoice::TYPES" :value="$invoice->type" required class="sm:col-span-2" />
                <x-input name="date" type="date" label="Date" :value="$invoice->date?->format('Y-m-d')" required />
                <x-input name="due_date" type="date" label="Échéance" :value="$invoice->due_date?->format('Y-m-d')" />
                <x-input name="subject" label="Objet" :value="$invoice->subject" class="sm:col-span-2" />
            </div>
        </div>

        @include('partials.line-editor', ['items' => old('items', $items), 'discount' => old('discount', $invoice->discount), 'taxRate' => old('tax_rate', $invoice->tax_rate)])

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="card {{ $invoice->exists ? 'lg:col-span-3' : 'lg:col-span-2' }}">
                <div class="card-body"><x-textarea name="notes" label="Notes affichées sur la facture" :value="$invoice->notes" rows="3" /></div>
            </div>
            @unless ($invoice->exists)
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Paiement reçu maintenant</h2></div>
                    <div class="card-body space-y-4">
                        <x-input name="paid_now" type="number" min="0" step="1" label="Montant ({{ setting('currency') }})" placeholder="0" hint="Laissez vide si le client paiera plus tard (dette)." />
                        <x-select name="paid_method" label="Mode de paiement" :options="\App\Support\Options::PAYMENT_METHODS" value="especes" />
                    </div>
                </div>
            @endunless
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ $invoice->exists ? route('invoices.show', $invoice) : route('invoices.index') }}" class="btn-secondary">Annuler</a>
            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer la facture</button>
        </div>
    </form>
</x-layouts.app>
