<x-layouts.app title="Paramètres">
    <x-page-header title="Paramètres" subtitle="Informations affichées sur vos devis et factures, valeurs par défaut et compte." />

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('settings.update') }}" class="card lg:col-span-2">
            @csrf @method('PUT')
            <div class="card-header"><h2 class="card-title">Entreprise & documents</h2></div>
            <div class="card-body grid gap-5 sm:grid-cols-2">
                <x-input name="company_name" label="Nom commercial" :value="setting('company_name')" required />
                <x-input name="company_tagline" label="Slogan / activité" :value="setting('company_tagline')" />
                <x-input name="company_phone" label="Téléphone(s)" :value="setting('company_phone')" />
                <x-input name="company_email" type="email" label="E-mail" :value="setting('company_email')" />
                <x-input name="company_address" label="Adresse" :value="setting('company_address')" class="sm:col-span-2" />
                <x-input name="company_rccm" label="RCCM" :value="setting('company_rccm')" />
                <x-input name="company_nif" label="NIF" :value="setting('company_nif')" />
                <x-input name="currency" label="Devise" :value="setting('currency')" required />
                <x-input name="default_tax_rate" type="number" step="0.01" min="0" max="100" label="TVA par défaut (%)" :value="setting('default_tax_rate')" required hint="0 si vous ne facturez pas la TVA." />
                <x-input name="quote_validity_days" type="number" min="1" label="Validité des devis (jours)" :value="setting('quote_validity_days')" required />
                <x-input name="invoice_due_days" type="number" min="0" label="Délai de paiement des factures (jours)" :value="setting('invoice_due_days')" required />
                <x-textarea name="quote_footer" label="Conditions par défaut des devis" :value="setting('quote_footer')" class="sm:col-span-2" />
                <x-textarea name="invoice_footer" label="Mention par défaut des factures" :value="setting('invoice_footer')" class="sm:col-span-2" />
            </div>
            <div class="flex justify-end border-t border-slate-100 px-5 py-4"><button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button></div>
        </form>

        <form method="POST" action="{{ route('settings.password') }}" class="card h-fit">
            @csrf @method('PUT')
            <div class="card-header"><h2 class="card-title">Mon compte</h2></div>
            <div class="card-body space-y-4">
                <x-input name="name" label="Nom" :value="auth()->user()->name" required />
                <x-input name="email" type="email" label="E-mail de connexion" :value="auth()->user()->email" required />
                <x-input name="password" type="password" label="Nouveau mot de passe" hint="Laisser vide pour ne pas changer." autocomplete="new-password" />
                <x-input name="password_confirmation" type="password" label="Confirmer" autocomplete="new-password" />
                <x-input name="current_password" type="password" label="Mot de passe actuel" required autocomplete="current-password" />
            </div>
            <div class="flex justify-end border-t border-slate-100 px-5 py-4"><button class="btn-primary">Mettre à jour</button></div>
        </form>
    </div>
</x-layouts.app>
