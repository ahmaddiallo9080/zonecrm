<x-layouts.app :title="$expense->exists ? 'Modifier la dépense' : 'Nouvelle dépense'">
    <x-page-header :title="$expense->exists ? 'Modifier la dépense' : 'Nouvelle dépense'" :back="route('expenses.index')"
        subtitle="Les achats de matériel pour le stock se saisissent dans « Achats fournisseurs »." />

    <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}" class="card max-w-3xl">
        @csrf
        @if ($expense->exists) @method('PUT') @endif
        <div class="card-body grid gap-5 sm:grid-cols-2">
            <x-input name="date" type="date" label="Date" :value="$expense->date?->format('Y-m-d')" required />
            <x-input name="amount" type="number" min="1" step="1" label="Montant ({{ setting('currency') }})" :value="$expense->amount ? (int) $expense->amount : null" required />
            <x-select name="category" label="Catégorie" :options="\App\Support\Options::EXPENSE_CATEGORIES" :value="$expense->category" required />
            <x-select name="method" label="Mode de paiement" :options="\App\Support\Options::PAYMENT_METHODS" :value="$expense->method" required />
            <x-input name="description" label="Description" :value="$expense->description" required class="sm:col-span-2" placeholder="Taxi moto chantier Ratoma, carburant groupe, recharge crédit…" />
            <x-select name="supplier_id" label="Fournisseur / bénéficiaire (optionnel)" :options="$suppliers" :value="$expense->supplier_id" placeholder="—" />
            <x-select name="client_id" label="Lié au chantier du client (optionnel)" :options="$clients" :value="$expense->client_id" placeholder="—" />
            <x-input name="reference" label="Référence / n° reçu" :value="$expense->reference" />
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('expenses.index') }}" class="btn-secondary">Annuler</a>
            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button>
        </div>
    </form>
</x-layouts.app>
