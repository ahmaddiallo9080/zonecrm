<x-layouts.app :title="$supplier->exists ? 'Modifier '.$supplier->name : 'Nouveau fournisseur'">
    <x-page-header :title="$supplier->exists ? 'Modifier le fournisseur' : 'Nouveau fournisseur'"
        :back="$supplier->exists ? route('suppliers.show', $supplier) : route('suppliers.index')" />

    <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="card max-w-3xl">
        @csrf
        @if ($supplier->exists) @method('PUT') @endif
        <div class="card-body grid gap-5 sm:grid-cols-2">
            <x-input name="name" label="Raison sociale" :value="$supplier->name" required class="sm:col-span-2" />
            <x-input name="contact_person" label="Personne à contacter" :value="$supplier->contact_person" />
            <x-input name="phone" label="Téléphone / WhatsApp" :value="$supplier->phone" />
            <x-input name="email" type="email" label="E-mail" :value="$supplier->email" />
            <x-input name="country" label="Pays" :value="$supplier->country" />
            <x-input name="address" label="Adresse" :value="$supplier->address" class="sm:col-span-2" />
            <x-textarea name="notes" label="Notes (délais, conditions, comptes de paiement…)" :value="$supplier->notes" class="sm:col-span-2" />
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ $supplier->exists ? route('suppliers.show', $supplier) : route('suppliers.index') }}" class="btn-secondary">Annuler</a>
            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button>
        </div>
    </form>
</x-layouts.app>
