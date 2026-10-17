<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · ZoneCRM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#3d63dd">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="grid min-h-full lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-ink lg:flex lg:flex-col lg:justify-between lg:p-12">
        <div class="absolute -top-32 -right-32 size-96 rounded-full bg-primary-600/30 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-20 size-96 rounded-full bg-primary-500/20 blur-3xl"></div>
        <div class="relative">
            <img src="{{ asset('images/logo-white.svg') }}" alt="ZoneCRM" class="h-11 w-auto">
        </div>
        <div class="relative">
            <h1 class="text-4xl leading-tight font-extrabold tracking-tight text-white">Vos clients, vos installations<br>et vos finances,<br><span class="text-primary-400">au même endroit.</span></h1>
            <p class="mt-4 max-w-md text-slate-400">Accès MikroTik & Starlink, devis, factures, stock, fournisseurs, dépenses et dettes — pensé pour les techniciens Wi-Fi Zone.</p>
        </div>
        <p class="relative text-xs text-slate-500">© {{ date('Y') }} ZoneCRM · {{ setting('company_name') }}</p>
    </div>

    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-sm">
            <img src="{{ asset('images/logo.svg') }}" alt="ZoneCRM" class="mb-10 h-10 w-auto lg:hidden">
            <h2 class="text-2xl font-extrabold tracking-tight text-ink">Connexion</h2>
            <p class="mt-1 text-sm text-slate-500">Entrez vos identifiants pour accéder au CRM.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <x-input name="email" type="email" label="Adresse e-mail" required autofocus autocomplete="username" />
                <x-input name="password" type="password" label="Mot de passe" required autocomplete="current-password" />
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"> Rester connecté
                </label>
                <button class="btn-primary w-full py-2.5">Se connecter</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
