@php $isInvoice = $kind === 'FACTURE'; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kind }} {{ $doc->number }} – {{ $doc->client->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 12mm; }
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none !important; margin: 0 !important; padding: 0 !important; max-width: none !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="bg-slate-200/70 py-8 print:py-0">
    <div class="no-print mx-auto mb-4 flex max-w-[210mm] items-center justify-between px-2">
        <a href="{{ $isInvoice ? route('invoices.show', $doc) : route('quotes.show', $doc) }}" class="btn-secondary">← Retour</a>
        <button onclick="window.print()" class="btn-primary">Imprimer / Enregistrer en PDF</button>
    </div>

    <div class="sheet mx-auto min-h-[297mm] max-w-[210mm] bg-white p-[14mm] text-[13px] text-slate-700 shadow-xl">
        {{-- En-tête --}}
        <div class="flex items-start justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/icon.svg') }}" alt="" class="size-12">
                    <div>
                        <p class="text-lg leading-tight font-extrabold text-ink">{{ setting('company_name') }}</p>
                        <p class="text-[11px] text-slate-500">{{ setting('company_tagline') }}</p>
                    </div>
                </div>
                <div class="mt-3 space-y-0.5 text-[11px] text-slate-500">
                    @if (setting('company_address'))<p>{{ setting('company_address') }}</p>@endif
                    <p>{{ collect([setting('company_phone'), setting('company_email')])->filter()->join(' · ') }}</p>
                    @if (setting('company_rccm') || setting('company_nif'))
                        <p>{{ collect([setting('company_rccm') ? 'RCCM : '.setting('company_rccm') : null, setting('company_nif') ? 'NIF : '.setting('company_nif') : null])->filter()->join(' · ') }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-extrabold tracking-tight text-primary-600">{{ $kind }}</p>
                <p class="mt-1 font-bold text-ink">N° {{ $doc->number }}</p>
                <p class="text-[11px] text-slate-500">Date : {{ fdate($doc->date) }}</p>
                @if ($isInvoice && $doc->due_date)<p class="text-[11px] text-slate-500">Échéance : {{ fdate($doc->due_date) }}</p>@endif
                @if (! $isInvoice && $doc->valid_until)<p class="text-[11px] text-slate-500">Valable jusqu'au : {{ fdate($doc->valid_until) }}</p>@endif
                @if ($isInvoice && $doc->is_cancelled)<p class="mt-1 inline-block rounded bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">ANNULÉE</p>@endif
            </div>
        </div>

        {{-- Client --}}
        <div class="mt-8 grid grid-cols-2 gap-6">
            <div>
                @if ($doc->subject)
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Objet</p>
                    <p class="mt-1 font-semibold text-ink">{{ $doc->subject }}</p>
                @endif
            </div>
            <div class="rounded-xl bg-primary-50 p-4">
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-700">{{ $isInvoice ? 'Facturé à' : 'Destinataire' }}</p>
                <p class="mt-1 text-sm font-bold text-ink">{{ $doc->client->name }}</p>
                @if ($doc->client->company)<p>{{ $doc->client->company }}</p>@endif
                <p class="text-[11px] text-slate-600">{{ collect([$doc->client->address, $doc->client->city])->filter()->join(', ') }}</p>
                <p class="text-[11px] text-slate-600">{{ collect([$doc->client->phone, $doc->client->email])->filter()->join(' · ') }}</p>
                <p class="text-[11px] text-slate-400">Réf. client : {{ $doc->client->code }}</p>
            </div>
        </div>

        {{-- Lignes --}}
        <table class="mt-8 w-full text-left">
            <thead>
                <tr class="bg-ink text-[10px] font-semibold tracking-wider text-white uppercase">
                    <th class="rounded-l-lg px-3 py-2.5">#</th>
                    <th class="px-3 py-2.5">Désignation</th>
                    <th class="px-3 py-2.5 text-right">Qté</th>
                    <th class="px-3 py-2.5 text-right">Prix unitaire</th>
                    <th class="rounded-r-lg px-3 py-2.5 text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($doc->items as $i => $item)
                    <tr class="border-b border-slate-100">
                        <td class="px-3 py-2.5 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-3 py-2.5 font-medium text-ink">{{ $item->description }}</td>
                        <td class="num px-3 py-2.5 text-right">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', ' '), '0'), ',') }}</td>
                        <td class="num px-3 py-2.5 text-right">{{ money($item->unit_price) }}</td>
                        <td class="num px-3 py-2.5 text-right font-semibold text-ink">{{ money($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totaux --}}
        <div class="mt-6 flex justify-end">
            <table class="w-72 text-sm">
                <tr><td class="py-1 text-slate-500">Sous-total</td><td class="num py-1 text-right font-semibold">{{ money($doc->subtotal) }}</td></tr>
                @if ($doc->discount > 0)<tr><td class="py-1 text-slate-500">Remise</td><td class="num py-1 text-right font-semibold">− {{ money($doc->discount) }}</td></tr>@endif
                @if ($doc->tax_rate > 0)<tr><td class="py-1 text-slate-500">TVA ({{ rtrim(rtrim(number_format($doc->tax_rate, 2, ',', ''), '0'), ',') }} %)</td><td class="num py-1 text-right font-semibold">{{ money($doc->tax_amount) }}</td></tr>@endif
                <tr class="border-t-2 border-ink"><td class="pt-2 font-bold text-ink">TOTAL</td><td class="num pt-2 text-right text-lg font-extrabold text-primary-700">{{ money($doc->total) }}</td></tr>
                @if ($isInvoice && $doc->amount_paid > 0)
                    <tr><td class="pt-2 text-slate-500">Déjà payé</td><td class="num pt-2 text-right font-semibold text-emerald-600">{{ money($doc->amount_paid) }}</td></tr>
                    <tr><td class="py-1 font-bold text-ink">Reste à payer</td><td class="num py-1 text-right font-extrabold {{ $doc->balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ money($doc->balance) }}</td></tr>
                @endif
            </table>
        </div>

        @if ($words = amount_in_words($doc->total))
            <p class="mt-6 text-[12px] text-slate-600">Arrêté{{ $isInvoice ? 'e la présente facture' : ' le présent devis' }} à la somme de : <span class="font-semibold text-ink">{{ $words }}</span>.</p>
        @endif

        @if ($doc->notes)
            <div class="mt-6 rounded-xl border border-slate-200 p-4 text-[12px] whitespace-pre-line text-slate-600">{{ $doc->notes }}</div>
        @endif

        <div class="mt-14 grid grid-cols-2 gap-10 text-[11px]">
            <div><p class="font-semibold text-slate-500">{{ $isInvoice ? 'Le client' : 'Bon pour accord (date & signature)' }}</p><div class="mt-12 border-t border-slate-300"></div></div>
            <div class="text-right"><p class="font-semibold text-slate-500">Pour {{ setting('company_name') }}</p><div class="mt-12 border-t border-slate-300"></div></div>
        </div>
    </div>
    @if (request('auto'))<script>window.addEventListener('load', () => window.print())</script>@endif
</body>
</html>
