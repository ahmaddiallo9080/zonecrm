<x-layouts.app :title="'Devis '.$quote->number">
    <x-page-header :title="'Devis '.$quote->number" :back="route('quotes.index')"
        subtitle="Émis le {{ fdate($quote->date) }} · valable jusqu'au {{ fdate($quote->valid_until) }}">
        <x-badge :color="$quote->status_color" class="text-xs">{{ $quote->isExpired() ? 'Expiré' : $quote->status_label }}</x-badge>
        <a href="{{ route('quotes.print', $quote) }}" target="_blank" class="btn-secondary"><x-icon name="printer" class="size-4" />Imprimer / PDF</a>
        <form method="POST" action="{{ route('quotes.duplicate', $quote) }}">@csrf<button class="btn-secondary"><x-icon name="copy" class="size-4" />Dupliquer</button></form>
        @if ($quote->status !== 'facture')
            <a href="{{ route('quotes.edit', $quote) }}" class="btn-secondary"><x-icon name="pencil" class="size-4" />Modifier</a>
            <form method="POST" action="{{ route('quotes.convert', $quote) }}" onsubmit="return confirm('Générer la facture ? Le stock du matériel sera décrémenté.')">
                @csrf<button class="btn-primary"><x-icon name="receipt" class="size-4" />Convertir en facture</button>
            </form>
        @elseif ($quote->invoice)
            <a href="{{ route('invoices.show', $quote->invoice) }}" class="btn-primary"><x-icon name="receipt" class="size-4" />Voir la facture {{ $quote->invoice->number }}</a>
        @endif
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">@include('partials.document-preview', ['doc' => $quote])</div>

        <div class="space-y-6">
            @if ($quote->status !== 'facture')
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Suivi du devis</h2></div>
                    <div class="card-body grid grid-cols-2 gap-2">
                        @foreach (['envoye' => 'Marquer envoyé', 'accepte' => 'Accepté', 'refuse' => 'Refusé', 'brouillon' => 'Brouillon'] as $st => $txt)
                            <form method="POST" action="{{ route('quotes.status', $quote) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $st }}">
                                <button class="btn-sm w-full {{ $quote->status === $st ? 'btn-primary' : 'btn-secondary' }}">{{ $txt }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            @php $wa = preg_replace('/\D/', '', $quote->client->phone2 ?: $quote->client->phone ?: ''); @endphp
            @if ($wa)
                <a target="_blank" class="card flex items-center gap-3 p-4 text-sm font-semibold text-ink hover:ring-emerald-300"
                   href="https://wa.me/{{ $wa }}?text={{ urlencode("Bonjour {$quote->client->name}, voici votre devis {$quote->number} d'un montant de ".money($quote->total).". Merci de nous confirmer votre accord. — ".setting('company_name')) }}">
                    <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-icon name="whatsapp" class="size-5" /></span>
                    Envoyer un message WhatsApp au client
                </a>
            @endif

            @if (! $quote->invoice)
                <x-delete-button :action="route('quotes.destroy', $quote)" label="Supprimer le devis" class="w-full" />
            @endif
        </div>
    </div>
</x-layouts.app>
