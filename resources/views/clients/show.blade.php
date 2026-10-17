<x-layouts.app :title="$client->name">
@php
    $balance = $client->balance;
    $invoiced = $client->invoices->where('is_cancelled', false)->sum('total');
    $paid = $client->invoices->where('is_cancelled', false)->sum('amount_paid');
    $wa = preg_replace('/\D/', '', $client->phone2 ?: $client->phone ?: '');
    $hasGps = $client->location && preg_match('/^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/', trim($client->location));
@endphp

    <x-page-header :title="$client->name" :back="route('clients.index')"
        subtitle="{{ $client->code }} · {{ $client->type === 'entreprise' ? 'Entreprise' : 'Particulier' }}{{ $client->company ? ' · '.e($client->company) : '' }}">
        <x-badge :color="$client->status_color" class="text-xs">{{ $client->status_label }}</x-badge>
        @if ($wa)
            <a href="https://wa.me/{{ $wa }}" target="_blank" class="btn-secondary"><x-icon name="whatsapp" class="size-4 text-emerald-600" />WhatsApp</a>
        @endif
        <a href="{{ route('interventions.create', ['client_id' => $client->id]) }}" class="btn-secondary"><x-icon name="wrench" class="size-4" />Intervention</a>
        <a href="{{ route('quotes.create', ['client_id' => $client->id]) }}" class="btn-secondary"><x-icon name="doc" class="size-4" />Devis</a>
        <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="btn-secondary"><x-icon name="receipt" class="size-4" />Facture</a>
        <a href="{{ route('clients.edit', $client) }}" class="btn-primary"><x-icon name="pencil" class="size-4" />Modifier</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Colonne infos --}}
        <div class="space-y-6">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Coordonnées</h2></div>
                <dl class="dl card-body space-y-4">
                    <div class="flex items-start gap-3"><x-icon name="phone" class="mt-0.5 size-4 text-slate-400" /><div><dt>Téléphone</dt><dd>{{ $client->phone ?: '—' }}@if ($client->phone2) <span class="text-slate-400">/ {{ $client->phone2 }}</span>@endif</dd></div></div>
                    <div class="flex items-start gap-3"><x-icon name="doc" class="mt-0.5 size-4 text-slate-400" /><div><dt>E-mail</dt><dd>{{ $client->email ?: '—' }}</dd></div></div>
                    <div class="flex items-start gap-3"><x-icon name="map" class="mt-0.5 size-4 text-slate-400" /><div><dt>Site</dt>
                        <dd>{{ collect([$client->address, $client->city])->filter()->join(', ') ?: '—' }}</dd>
                        @if ($client->location)
                            <dd class="text-xs text-slate-500">
                                @if ($hasGps)<a href="https://maps.google.com/?q={{ urlencode($client->location) }}" target="_blank" class="font-semibold text-primary-600 hover:underline">{{ $client->location }} ↗</a>@else{{ $client->location }}@endif
                            </dd>
                        @endif
                    </div></div>
                    <div class="flex items-start gap-3"><x-icon name="calendar" class="mt-0.5 size-4 text-slate-400" /><div><dt>Client depuis</dt><dd>{{ fdate($client->created_at) }}</dd></div></div>
                </dl>
                @if ($client->notes)
                    <div class="border-t border-slate-100 px-5 py-4 text-sm whitespace-pre-line text-slate-600">{{ $client->notes }}</div>
                @endif
            </div>

            <div class="card">
                <div class="card-header"><h2 class="card-title">Situation financière</h2></div>
                <div class="card-body space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">Total facturé</span><span class="num font-semibold text-ink">{{ money($invoiced) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Total payé</span><span class="num font-semibold text-emerald-600">{{ money($paid) }}</span></div>
                    <div class="flex justify-between border-t border-dashed border-slate-200 pt-3"><span class="font-semibold text-ink">Reste à payer</span><span class="num text-lg font-extrabold {{ $balance > 0 ? 'text-red-600' : 'text-slate-400' }}">{{ money($balance) }}</span></div>
                </div>
            </div>

            <x-delete-button :action="route('clients.destroy', $client)" label="Archiver le client" class="w-full" confirm="Archiver ce client ? Ses documents restent consultables." />
        </div>

        {{-- Onglets --}}
        <div class="lg:col-span-2" x-data="{ tab: location.hash === '#acces' || !location.hash ? 'acces' : location.hash.slice(1) }">
            <div class="mb-5 flex gap-6 overflow-x-auto border-b border-slate-200">
                @foreach (['acces' => 'Accès techniques ('.$client->accesses->count().')', 'interventions' => 'Interventions ('.$client->interventions->count().')', 'devis' => 'Devis ('.$client->quotes->count().')', 'factures' => 'Factures ('.$client->invoices->count().')', 'paiements' => 'Paiements ('.$client->payments->count().')'] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'; history.replaceState(null, '', '#{{ $key }}')" :class="tab === '{{ $key }}' && 'active'" class="tab">{{ $label }}</button>
                @endforeach
            </div>

            {{-- ACCÈS --}}
            <div x-show="tab === 'acces'" x-data="accessManager()">
                <div class="mb-4 flex items-center justify-between rounded-xl bg-primary-50 px-4 py-3 ring-1 ring-primary-100">
                    <p class="flex items-center gap-2 text-xs font-medium text-primary-800"><x-icon name="key" class="size-4" />Mots de passe chiffrés en base et chargés uniquement à la demande.</p>
                    <button type="button" class="btn-primary btn-sm" @click="openNew()"><x-icon name="plus" class="size-3.5" />Ajouter un accès</button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @forelse ($client->accesses as $a)
                        <div class="card p-5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <x-badge :color="$a->type_color">{{ $a->type_label }}</x-badge>
                                    <p class="mt-2 truncate font-bold text-ink">{{ $a->label }}</p>
                                </div>
                                <div class="flex shrink-0">
                                    <button type="button" class="btn-ghost" title="Modifier"
                                        @click="openEdit(@js(['id' => $a->id, 'type' => $a->type, 'label' => $a->label, 'host' => $a->host, 'port' => $a->port, 'username' => $a->username, 'serial_number' => $a->serial_number, 'plan' => $a->plan, 'renewal_date' => $a->renewal_date?->format('Y-m-d'), 'has_password' => filled($a->getRawOriginal('password')), 'has_extra' => filled($a->getRawOriginal('extra'))]), '{{ route('accesses.update', $a) }}')">
                                        <x-icon name="pencil" class="size-4" />
                                    </button>
                                    <x-delete-button :action="route('accesses.destroy', $a)" confirm="Supprimer cet accès ?" />
                                </div>
                            </div>
                            <dl class="mt-4 space-y-2.5 text-sm">
                                @if ($a->host)
                                    @php $isUrl = str_starts_with($a->host, 'http'); $link = $isUrl ? $a->host : 'http://'.$a->host.($a->port ? ':'.$a->port : ''); @endphp
                                    <div class="flex items-center justify-between gap-2"><dt class="text-slate-400">Adresse</dt>
                                        <dd class="truncate font-mono text-xs"><a href="{{ $link }}" target="_blank" class="text-primary-600 hover:underline">{{ $a->host }}{{ $a->port && ! $isUrl ? ':'.$a->port : '' }}</a></dd></div>
                                @endif
                                @if ($a->username)
                                    <div class="flex items-center justify-between gap-2" x-data="copyable(@js($a->username))"><dt class="text-slate-400">Identifiant</dt>
                                        <dd class="flex items-center gap-1 font-mono text-xs text-ink">{{ $a->username }}
                                            <button type="button" @click="copy" class="text-slate-400 hover:text-primary-600"><x-icon name="copy" class="size-3.5" x-show="!copied" /><x-icon name="check" class="size-3.5 text-emerald-600" x-show="copied" x-cloak /></button>
                                        </dd></div>
                                @endif
                                @if (filled($a->getRawOriginal('password')))
                                    <div class="flex items-center justify-between gap-2" x-data="secret('{{ route('accesses.reveal', $a) }}')"><dt class="text-slate-400">Mot de passe</dt>
                                        <dd class="flex items-center gap-1 font-mono text-xs text-ink">
                                            <span x-text="shown ? value : '••••••••••'"></span>
                                            <button type="button" @click="toggle" class="text-slate-400 hover:text-primary-600" :title="shown ? 'Masquer' : 'Afficher'"><x-icon name="eye" class="size-3.5" x-show="!shown" /><x-icon name="eye-off" class="size-3.5" x-show="shown" x-cloak /></button>
                                            <button type="button" @click="copy" class="text-slate-400 hover:text-primary-600" title="Copier"><x-icon name="copy" class="size-3.5" x-show="!copied" /><x-icon name="check" class="size-3.5 text-emerald-600" x-show="copied" x-cloak /></button>
                                        </dd></div>
                                @endif
                                @if ($a->serial_number)<div class="flex justify-between gap-2"><dt class="text-slate-400">N° série / kit</dt><dd class="font-mono text-xs text-ink">{{ $a->serial_number }}</dd></div>@endif
                                @if ($a->plan)<div class="flex justify-between gap-2"><dt class="text-slate-400">Abonnement / version</dt><dd class="text-ink">{{ $a->plan }}</dd></div>@endif
                                @if ($a->renewal_date)<div class="flex justify-between gap-2"><dt class="text-slate-400">Renouvellement</dt><dd><x-badge :color="$a->renewal_soon ? 'red' : 'gray'">{{ fdate($a->renewal_date) }}</x-badge></dd></div>@endif
                                @if (filled($a->getRawOriginal('extra')))
                                    <div x-data="secret('{{ route('accesses.reveal', $a) }}', 'extra')" class="border-t border-slate-100 pt-2.5">
                                        <button type="button" @click="toggle" class="text-xs font-semibold text-primary-600 hover:underline" x-text="shown ? 'Masquer les notes' : 'Voir les notes sécurisées'"></button>
                                        <p x-show="shown" x-cloak x-text="value" class="mt-1.5 rounded-lg bg-slate-50 p-2.5 font-mono text-xs whitespace-pre-line text-slate-700"></p>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @empty
                        <div class="card sm:col-span-2"><x-empty icon="key" title="Aucun accès enregistré" text="Ajoutez les identifiants MikroTik, le compte Starlink, le SSID Wi-Fi…" /></div>
                    @endforelse
                </div>

                {{-- Modale ajout / édition --}}
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-ink/50 p-4 sm:p-10" @keydown.escape.window="open = false">
                    <form method="POST" :action="action" class="card w-full max-w-2xl" @click.outside="open = false">
                        @csrf
                        <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="card-header">
                            <h3 class="card-title" x-text="editing ? 'Modifier l\'accès' : 'Nouvel accès'"></h3>
                            <button type="button" class="btn-ghost" @click="open = false"><x-icon name="x" class="size-4" /></button>
                        </div>
                        <div class="card-body grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="label">Type *</label>
                                <select name="type" x-model="f.type" class="input" required>
                                    @foreach (\App\Support\Options::ACCESS_TYPES as $k => [$l]) <option value="{{ $k }}">{{ $l }}</option> @endforeach
                                </select>
                            </div>
                            <div><label class="label">Libellé *</label><input name="label" x-model="f.label" class="input" required :placeholder="hints[f.type]?.label"></div>
                            <div><label class="label" x-text="f.type === 'starlink' ? 'URL / app' : 'Adresse IP / domaine'"></label><input name="host" x-model="f.host" class="input font-mono" :placeholder="hints[f.type]?.host"></div>
                            <div><label class="label">Port</label><input name="port" type="number" x-model="f.port" class="input font-mono" :placeholder="hints[f.type]?.port"></div>
                            <div><label class="label" x-text="f.type === 'starlink' ? 'E-mail du compte' : (f.type === 'wifi' ? 'SSID' : 'Identifiant')"></label><input name="username" x-model="f.username" class="input font-mono" autocomplete="off"></div>
                            <div>
                                <label class="label">Mot de passe</label>
                                <input name="password" type="text" class="input font-mono" autocomplete="new-password" :placeholder="editing && f.has_password ? 'Laisser vide pour conserver' : ''">
                            </div>
                            <div><label class="label" x-text="f.type === 'starlink' ? 'N° du kit / terminal' : 'N° de série'"></label><input name="serial_number" x-model="f.serial_number" class="input font-mono"></div>
                            <div><label class="label" x-text="f.type === 'starlink' ? 'Abonnement' : 'Modèle / version'"></label><input name="plan" x-model="f.plan" class="input" :placeholder="hints[f.type]?.plan"></div>
                            <div><label class="label">Date de renouvellement</label><input name="renewal_date" type="date" x-model="f.renewal_date" class="input"></div>
                            <div class="sm:col-span-2">
                                <label class="label">Notes sécurisées (chiffrées)</label>
                                <textarea name="extra" rows="3" class="input font-mono text-xs" :placeholder="editing && f.has_extra ? 'Laisser vide pour conserver les notes existantes' : 'Clés API, PIN, utilisateurs hotspot, configuration…'"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
                            <button type="button" class="btn-secondary" @click="open = false">Annuler</button>
                            <button class="btn-primary"><x-icon name="check" class="size-4" />Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- INTERVENTIONS --}}
            <div x-show="tab === 'interventions'" x-cloak class="card overflow-hidden">
                @forelse ($client->interventions as $it)
                    <a href="{{ route('interventions.show', $it) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 last:border-0 hover:bg-slate-50">
                        <div class="min-w-0"><p class="truncate text-sm font-semibold text-ink">{{ $it->title }}</p><p class="text-xs text-slate-500">{{ $it->type_label }} · {{ $it->scheduled_at?->format('d/m/Y H:i') ?? 'Non planifiée' }}</p></div>
                        <x-badge :color="$it->status_color">{{ $it->status_label }}</x-badge>
                    </a>
                @empty
                    <x-empty icon="wrench" title="Aucune intervention"><a href="{{ route('interventions.create', ['client_id' => $client->id]) }}" class="btn-primary btn-sm">Planifier</a></x-empty>
                @endforelse
            </div>

            {{-- DEVIS --}}
            <div x-show="tab === 'devis'" x-cloak class="card overflow-hidden">
                @forelse ($client->quotes as $q)
                    <a href="{{ route('quotes.show', $q) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 last:border-0 hover:bg-slate-50">
                        <div><p class="text-sm font-semibold text-ink">{{ $q->number }}</p><p class="text-xs text-slate-500">{{ fdate($q->date) }} · {{ $q->subject ?: 'Sans objet' }}</p></div>
                        <div class="flex items-center gap-3"><span class="num text-sm font-bold text-ink">{{ money($q->total) }}</span><x-badge :color="$q->status_color">{{ $q->status_label }}</x-badge></div>
                    </a>
                @empty
                    <x-empty icon="doc" title="Aucun devis"><a href="{{ route('quotes.create', ['client_id' => $client->id]) }}" class="btn-primary btn-sm">Créer un devis</a></x-empty>
                @endforelse
            </div>

            {{-- FACTURES --}}
            <div x-show="tab === 'factures'" x-cloak class="card overflow-hidden">
                @forelse ($client->invoices as $inv)
                    <a href="{{ route('invoices.show', $inv) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 last:border-0 hover:bg-slate-50">
                        <div><p class="text-sm font-semibold text-ink">{{ $inv->number }}</p><p class="text-xs text-slate-500">{{ fdate($inv->date) }} · {{ \App\Models\Invoice::TYPES[$inv->type] ?? $inv->type }}</p></div>
                        <div class="text-right"><p class="num text-sm font-bold text-ink">{{ money($inv->total) }}</p>@if ($inv->balance > 0)<p class="num text-xs font-semibold text-red-600">reste {{ money($inv->balance) }}</p>@endif</div>
                    </a>
                @empty
                    <x-empty icon="receipt" title="Aucune facture"><a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="btn-primary btn-sm">Créer une facture</a></x-empty>
                @endforelse
            </div>

            {{-- PAIEMENTS --}}
            <div x-show="tab === 'paiements'" x-cloak class="card overflow-hidden">
                @forelse ($client->payments->sortByDesc('date') as $p)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 last:border-0">
                        <div><p class="text-sm font-semibold text-ink">{{ fdate($p->date) }} · {{ $p->method_label }}</p><p class="text-xs text-slate-500">Facture <a href="{{ route('invoices.show', $p->invoice_id) }}" class="text-primary-600 hover:underline">{{ $p->invoice?->number }}</a>{{ $p->reference ? ' · Réf. '.$p->reference : '' }}</p></div>
                        <span class="num text-sm font-bold text-emerald-600">+ {{ money($p->amount) }}</span>
                    </div>
                @empty
                    <x-empty icon="banknotes" title="Aucun paiement reçu" />
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function accessManager() {
            const blank = { type: 'mikrotik', label: '', host: '', port: '', username: '', serial_number: '', plan: '', renewal_date: '', has_password: false, has_extra: false };
            return {
                open: false, editing: false, action: '', f: { ...blank },
                hints: {
                    mikrotik: { label: 'Routeur principal (hAP ax²)', host: '192.168.88.1', port: '8291', plan: 'RouterOS 7.x' },
                    starlink: { label: 'Kit Starlink du site', host: 'starlink.com', port: '', plan: 'Standard / Résidentiel' },
                    wifi: { label: 'Réseau clients', host: '', port: '', plan: '' },
                    routeur: { label: 'Point d\'accès extérieur', host: '192.168.88.2', port: '80', plan: '' },
                    serveur: { label: 'Serveur hotspot / RADIUS', host: '', port: '', plan: '' },
                    autre: { label: '', host: '', port: '', plan: '' },
                },
                openNew() { this.f = { ...blank }; this.editing = false; this.action = @js(route('clients.accesses.store', $client)); this.open = true; },
                openEdit(data, url) { this.f = { ...blank, ...data }; this.editing = true; this.action = url; this.open = true; },
            };
        }
    </script>
    @endpush
</x-layouts.app>
