{{-- Aperçu à l'écran d'un devis ou d'une facture. Variables : $doc --}}
<div class="card overflow-hidden">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 p-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Client</p>
            <a href="{{ route('clients.show', $doc->client_id) }}" class="mt-1 block text-base font-bold text-ink hover:text-primary-700">{{ $doc->client->name }}</a>
            @if ($doc->client->company)<p class="text-sm text-slate-600">{{ $doc->client->company }}</p>@endif
            <p class="text-sm text-slate-500">{{ collect([$doc->client->phone, $doc->client->address, $doc->client->city])->filter()->join(' · ') }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Objet</p>
            <p class="mt-1 max-w-xs text-sm font-medium text-ink">{{ $doc->subject ?: '—' }}</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="table">
            <thead><tr><th>Désignation</th><th class="text-right">Qté</th><th class="text-right">P.U.</th><th class="text-right">Total</th></tr></thead>
            <tbody>
                @foreach ($doc->items as $item)
                    <tr class="hover:!bg-transparent">
                        <td class="font-medium text-ink">{{ $item->description }}
                            @if ($item->product?->type === 'produit')<span class="ml-1 text-[10px] font-semibold uppercase text-slate-400">matériel</span>@endif
                        </td>
                        <td class="num text-right">{{ rtrim(rtrim(number_format($item->quantity, 2, ',', ' '), '0'), ',') }}</td>
                        <td class="num text-right">{{ money($item->unit_price) }}</td>
                        <td class="num text-right font-semibold text-ink">{{ money($item->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex justify-end border-t border-slate-100 p-6">
        <dl class="w-full max-w-xs space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Sous-total</dt><dd class="num font-semibold">{{ money($doc->subtotal) }}</dd></div>
            @if ($doc->discount > 0)<div class="flex justify-between"><dt class="text-slate-500">Remise</dt><dd class="num font-semibold text-red-600">− {{ money($doc->discount) }}</dd></div>@endif
            @if ($doc->tax_rate > 0)<div class="flex justify-between"><dt class="text-slate-500">TVA {{ rtrim(rtrim(number_format($doc->tax_rate, 2, ',', ''), '0'), ',') }} %</dt><dd class="num font-semibold">{{ money($doc->tax_amount) }}</dd></div>@endif
            <div class="flex justify-between border-t border-dashed border-slate-200 pt-2"><dt class="font-bold text-ink">Total</dt><dd class="num text-lg font-extrabold text-primary-700">{{ money($doc->total) }}</dd></div>
            @if (! empty($showPaid))
                <div class="flex justify-between"><dt class="text-slate-500">Déjà payé</dt><dd class="num font-semibold text-emerald-600">{{ money($doc->amount_paid) }}</dd></div>
                <div class="flex justify-between rounded-lg {{ $doc->balance > 0 ? 'bg-red-50' : 'bg-emerald-50' }} px-3 py-2"><dt class="font-bold {{ $doc->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">Reste à payer</dt><dd class="num font-extrabold {{ $doc->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ money($doc->balance) }}</dd></div>
            @endif
        </dl>
    </div>
    @if ($doc->notes)
        <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-4 text-sm whitespace-pre-line text-slate-600">{{ $doc->notes }}</div>
    @endif
</div>
