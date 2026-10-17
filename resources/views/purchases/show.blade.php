<x-layouts.app :title="'Achat '.$purchase->number">
    <x-page-header :title="'Achat '.$purchase->number" :back="route('purchases.index')"
        subtitle="<a class='text-primary-600 hover:underline' href='{{ route('suppliers.show', $purchase->supplier_id) }}'>{{ e($purchase->supplier->name) }}</a> · {{ fdate($purchase->date) }}{{ $purchase->supplier_ref ? ' · Réf. '.e($purchase->supplier_ref) : '' }}">
        <x-badge :color="$purchase->status_color" class="text-xs">{{ $purchase->status_label }}</x-badge>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn-secondary"><x-icon name="pencil" class="size-4" />Modifier</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card overflow-hidden lg:col-span-2">
            <table class="table">
                <thead><tr><th>Désignation</th><th class="text-right">Qté</th><th class="text-right">Coût unitaire</th><th class="text-right">Total</th></tr></thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        <tr class="hover:!bg-transparent">
                            <td class="font-medium text-ink">@if ($item->product)<a href="{{ route('products.show', $item->product) }}" class="hover:text-primary-700">{{ $item->description }}</a>@else{{ $item->description }}@endif</td>
                            <td class="num text-right">{{ $item->quantity + 0 }}</td>
                            <td class="num text-right">{{ money($item->unit_cost) }}</td>
                            <td class="num text-right font-semibold text-ink">{{ money($item->total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <dl class="space-y-2 border-t border-slate-100 p-6 text-sm sm:ml-auto sm:max-w-xs">
                <div class="flex justify-between"><dt class="font-bold text-ink">Total</dt><dd class="num text-lg font-extrabold text-ink">{{ money($purchase->total) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Payé</dt><dd class="num font-semibold text-emerald-600">{{ money($purchase->amount_paid) }}</dd></div>
                <div class="flex justify-between rounded-lg {{ $purchase->balance > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-2"><dt class="font-bold">Reste dû</dt><dd class="num font-extrabold">{{ money($purchase->balance) }}</dd></div>
            </dl>
            @if ($purchase->notes)<div class="border-t border-slate-100 px-6 py-4 text-sm whitespace-pre-line text-slate-600">{{ $purchase->notes }}</div>@endif
        </div>

        <div class="space-y-6">
            @if ($purchase->balance > 0)
                <div class="card ring-2 ring-primary-100">
                    <div class="card-header"><h2 class="card-title">Payer le fournisseur</h2></div>
                    <form method="POST" action="{{ route('purchases.payments.store', $purchase) }}" class="card-body space-y-4" x-data="{ amount: '' }">
                        @csrf
                        <div>
                            <label class="label">Montant *</label>
                            <div class="flex gap-2">
                                <input name="amount" type="number" min="1" step="1" x-model="amount" class="input num" required>
                                <button type="button" class="btn-secondary btn-sm" @click="amount = {{ (int) $purchase->balance }}">Solde</button>
                            </div>
                            @error('amount')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <x-input name="date" type="date" label="Date" :value="today()->format('Y-m-d')" required />
                            <x-select name="method" label="Mode" :options="\App\Support\Options::PAYMENT_METHODS" value="especes" required />
                        </div>
                        <x-input name="reference" label="Référence" />
                        <button class="btn-primary w-full"><x-icon name="banknotes" class="size-4" />Enregistrer le paiement</button>
                    </form>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h2 class="card-title">Paiements effectués</h2></div>
                @forelse ($purchase->payments as $p)
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0">
                        <div><p class="num text-sm font-bold text-ink">− {{ money($p->amount) }}</p><p class="text-xs text-slate-500">{{ fdate($p->date) }} · {{ $p->method_label }}{{ $p->reference ? ' · '.$p->reference : '' }}</p></div>
                        <x-delete-button :action="route('purchase-payments.destroy', $p)" confirm="Supprimer ce paiement ?" />
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-400">Aucun paiement.</p>
                @endforelse
            </div>

            <x-delete-button :action="route('purchases.destroy', $purchase)" label="Supprimer l'achat" class="w-full" confirm="Supprimer cet achat ? Les quantités seront retirées du stock." />
        </div>
    </div>
</x-layouts.app>
