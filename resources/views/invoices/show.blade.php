<x-layouts.app :title="'Facture '.$invoice->number">
@php
    $sub = e(\App\Models\Invoice::TYPES[$invoice->type] ?? '').' · émise le '.fdate($invoice->date).' · échéance '.fdate($invoice->due_date);
    if ($invoice->quote) {
        $sub .= ' · devis <a class="text-primary-600 hover:underline" href="'.route('quotes.show', $invoice->quote).'">'.e($invoice->quote->number).'</a>';
    }
@endphp
    <x-page-header :title="'Facture '.$invoice->number" :back="route('invoices.index')" :subtitle="$sub">
        <x-badge :color="$invoice->status_color" class="text-xs">{{ $invoice->status_label }}</x-badge>
        <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-secondary"><x-icon name="printer" class="size-4" />Imprimer / PDF</a>
        @unless ($invoice->is_cancelled)
            <a href="{{ route('invoices.edit', $invoice) }}" class="btn-secondary"><x-icon name="pencil" class="size-4" />Modifier</a>
        @endunless
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">@include('partials.document-preview', ['doc' => $invoice, 'showPaid' => true])</div>

        <div class="space-y-6">
            @if ($invoice->balance > 0)
                <div class="card ring-2 ring-primary-100">
                    <div class="card-header"><h2 class="card-title">Enregistrer un paiement</h2></div>
                    <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="card-body space-y-4" x-data="{ amount: '' }">
                        @csrf
                        <div>
                            <label class="label">Montant *</label>
                            <div class="flex gap-2">
                                <input name="amount" type="number" min="1" step="1" x-model="amount" class="input num" required>
                                <button type="button" class="btn-secondary btn-sm" @click="amount = {{ (int) $invoice->balance }}">Solde</button>
                            </div>
                            @error('amount')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input name="date" type="date" label="Date" :value="today()->format('Y-m-d')" required />
                            <x-select name="method" label="Mode" :options="\App\Support\Options::PAYMENT_METHODS" value="especes" required />
                        </div>
                        <x-input name="reference" label="Référence (n° transaction…)" />
                        <button class="btn-success w-full"><x-icon name="banknotes" class="size-4" />Valider le paiement</button>
                    </form>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h2 class="card-title">Historique des paiements</h2></div>
                @forelse ($invoice->payments as $p)
                    <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-3 last:border-0">
                        <div>
                            <p class="num text-sm font-bold text-emerald-600">+ {{ money($p->amount) }}</p>
                            <p class="text-xs text-slate-500">{{ fdate($p->date) }} · {{ $p->method_label }}{{ $p->reference ? ' · '.$p->reference : '' }}</p>
                        </div>
                        <x-delete-button :action="route('invoice-payments.destroy', $p)" confirm="Supprimer ce paiement ?" />
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">Aucun paiement enregistré.</p>
                @endforelse
            </div>

            @php $wa = preg_replace('/\D/', '', $invoice->client->phone2 ?: $invoice->client->phone ?: ''); @endphp
            @if ($wa && $invoice->balance > 0)
                <a target="_blank" class="card flex items-center gap-3 p-4 text-sm font-semibold text-ink hover:ring-emerald-300"
                   href="https://wa.me/{{ $wa }}?text={{ urlencode("Bonjour {$invoice->client->name}, petit rappel concernant la facture {$invoice->number} : il reste ".money($invoice->balance)." à régler. Merci ! — ".setting('company_name')) }}">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-icon name="whatsapp" class="size-5" /></span>
                    Relancer le client sur WhatsApp
                </a>
            @endif

            @unless ($invoice->is_cancelled)
                <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Annuler cette facture ? Le matériel sera remis en stock.')">
                    @csrf<button class="btn-secondary w-full">Annuler la facture</button>
                </form>
            @endunless
            <x-delete-button :action="route('invoices.destroy', $invoice)" label="Supprimer la facture" class="w-full" />
        </div>
    </div>
</x-layouts.app>
