<x-layouts.app :title="$client->exists ? 'Modifier '.$client->name : 'Nouveau client'">
    <x-page-header :title="$client->exists ? 'Modifier le client' : 'Nouveau client'"
        :subtitle="$client->exists ? $client->code.' · '.$client->name : 'Les accès MikroTik / Starlink s\'ajoutent sur la fiche après création.'"
        :back="$client->exists ? route('clients.show', $client) : route('clients.index')" />

    <form method="POST" action="{{ $client->exists ? route('clients.update', $client) : route('clients.store') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($client->exists) @method('PUT') @endif

        <div class="card lg:col-span-2">
            <div class="card-header"><h2 class="card-title">Identité & contact</h2></div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <x-select name="type" label="Type" :options="['particulier' => 'Particulier', 'entreprise' => 'Entreprise / Organisation']" :value="$client->type" required />
                <x-select name="status" label="Statut" :options="\App\Models\Client::STATUSES" :value="$client->status" required />
                <x-input name="name" label="Nom complet / responsable" :value="$client->name" required />
                <x-input name="company" label="Entreprise / nom de la zone Wi-Fi" :value="$client->company" />
                <x-input name="phone" label="Téléphone" :value="$client->phone" placeholder="+224 6xx xx xx xx" />
                <x-input name="phone2" label="Téléphone 2 / WhatsApp" :value="$client->phone2" />
                <x-input name="email" type="email" label="E-mail" :value="$client->email" class="sm:col-span-2" />
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2 class="card-title">Localisation du site</h2></div>
            <div class="card-body space-y-5">
                <x-input name="city" label="Ville / préfecture" :value="$client->city" />
                <x-input name="address" label="Quartier / adresse" :value="$client->address" />
                <x-input name="location" label="Repère ou coordonnées GPS" :value="$client->location" placeholder="9.5370, -13.6773" hint="Collez des coordonnées pour ouvrir la carte depuis la fiche." />
            </div>
        </div>

        <div class="card lg:col-span-3">
            <div class="card-body">
                <x-textarea name="notes" label="Notes internes" :value="$client->notes" rows="4" placeholder="Matériel installé, particularités du site, préférences du client…" />
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
                <a href="{{ $client->exists ? route('clients.show', $client) : route('clients.index') }}" class="btn-secondary">Annuler</a>
                <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button>
            </div>
        </div>
    </form>
</x-layouts.app>
