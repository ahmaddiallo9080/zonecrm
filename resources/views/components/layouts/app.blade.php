<!DOCTYPE html>
<html lang="fr" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="currency" content="{{ setting('currency') }}">
    <title>{{ isset($title) ? $title . ' · ' : '' }}ZoneCRM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.svg') }}">
    <meta name="theme-color" content="#3d63dd">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full" x-data="{ nav: false }">
    @php
        $menu = [
            ['', [['dashboard', 'Tableau de bord', 'home', 'dashboard']]],
            [
                'Activité',
                [
                    ['clients.index', 'Clients', 'users', 'clients.*'],
                    ['interventions.index', 'Interventions', 'wrench', 'interventions.*'],
                    ['quotes.index', 'Devis', 'doc', 'quotes.*'],
                    ['invoices.index', 'Factures', 'receipt', 'invoices.*'],
                ],
            ],
            [
                'Stock & achats',
                [
                    ['products.index', 'Produits & services', 'box', 'products.*'],
                    ['suppliers.index', 'Fournisseurs', 'truck', 'suppliers.*'],
                    ['purchases.index', 'Achats fournisseurs', 'cart', 'purchases.*'],
                ],
            ],
            [
                'Finances',
                [
                    ['debts.index', 'Dettes', 'scale', 'debts.*'],
                    ['expenses.index', 'Dépenses', 'wallet', 'expenses.*'],
                    ['reports.index', 'Trésorerie', 'chart', 'reports.*'],
                ],
            ],
        ];
    @endphp

    {{-- Overlay mobile --}}
    <div x-show="nav" x-cloak x-transition.opacity class="fixed inset-0 z-30 bg-ink/50 lg:hidden"
        @click="nav = false"></div>

    {{-- Sidebar --}}
    <aside :class="nav ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink transition-transform duration-200">
        <div class="flex h-16 items-center justify-between gap-2 px-5">
            <a href="{{ route('dashboard') }}" class="block">
                <img src="{{ asset('images/logo-white.svg') }}" alt="ZoneCRM" class="h-8 w-auto">
            </a>
            <button class="btn-ghost -mr-2 text-slate-400 hover:bg-white/5 hover:text-white lg:hidden"
                @click="nav = false" title="Fermer">
                <x-icon name="x" class="size-5" />
            </button>
        </div>
        {{-- <p class="truncate px-5 pb-1 text-[11px] font-medium text-slate-500">{{ setting('company_name') }}</p> --}}

        <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 pb-6">
            @foreach ($menu as [$section, $links])
                <p class="nav-section">{{ $section }}</p>
                <div class="space-y-0.5">
                    @foreach ($links as [$route, $text, $icon, $pattern])
                        <a href="{{ route($route) }}"
                            class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                            <x-icon :name="$icon" class="size-[18px]" /> {{ $text }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="border-t border-white/5 p-3">
            <a href="{{ route('settings.edit') }}"
                class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <x-icon name="cog" class="size-[18px]" /> Paramètres
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-link w-full"><x-icon name="logout" class="size-[18px]" /> Déconnexion</button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-64">
        {{-- Topbar --}}
        <header
            class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200/70 bg-white/80 px-4 backdrop-blur sm:px-6">
            <button class="btn-ghost lg:hidden" @click="nav = true"><x-icon name="menu" /></button>
            <a href="{{ route('dashboard') }}" class="shrink-0 lg:hidden"><img src="{{ asset('images/icon.svg') }}"
                    alt="ZoneCRM" class="size-8"></a>
            <form action="{{ route('search') }}" class="relative w-full max-w-md">
                <x-icon name="search"
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
                <input name="q" value="{{ request('q') }}"
                    placeholder="Rechercher client, facture, devis, produit…"
                    class="input bg-slate-50 pl-9 ring-transparent focus:bg-white">
            </form>
            <div class="ml-auto flex items-center gap-2">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="btn-primary"><x-icon name="plus" class="size-4" /><span
                            class="hidden sm:inline">Nouveau</span></button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition
                        class="absolute right-0 mt-2 w-52 overflow-hidden rounded-xl bg-white py-1.5 shadow-xl ring-1 ring-slate-200">
                        @foreach ([['clients.create', 'Client', 'users'], ['interventions.create', 'Intervention', 'wrench'], ['quotes.create', 'Devis', 'doc'], ['invoices.create', 'Facture', 'receipt'], ['purchases.create', 'Achat fournisseur', 'cart'], ['expenses.create', 'Dépense', 'wallet']] as [$r, $t, $i])
                            <a href="{{ route($r) }}"
                                class="flex items-center gap-2.5 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-primary-50 hover:text-primary-700">
                                <x-icon :name="$i" class="size-4" /> {{ $t }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="hidden items-center gap-2.5 border-l border-slate-200 pl-3 sm:flex">
                    <div
                        class="flex size-8 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <span class="text-sm font-semibold text-ink">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                    class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">
                    <x-icon name="check" class="size-5 text-emerald-600" /> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div
                    class="mb-5 flex items-center gap-3 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-800 ring-1 ring-red-200">
                    <x-icon name="alert" class="size-5 text-red-600" /> {{ session('error') }}
                </div>
            @endif
            @if ($errors->any() && !isset($hideErrorBanner))
                <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
                    <p class="font-semibold">Veuillez corriger les erreurs du formulaire.</p>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
</body>

</html>
