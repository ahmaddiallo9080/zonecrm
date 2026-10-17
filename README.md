# GC-Manager — CRM pour techniciens Hotspot / Wi-Fi Zone

Application Laravel 12 + MySQL + Vite (Tailwind CSS 4, Alpine.js, Chart.js) pour gérer une activité
d'installation, de dépannage et de vente de matériel Wi-Fi Zone.

## Modules

| Module | Ce qu'il fait |
|---|---|
| **Tableau de bord** | Encaissements / sorties du mois, créances et dettes, graphique 6 mois, interventions à venir, factures en retard, stock faible, renouvellements Starlink proches |
| **Clients** | Fiche client avec 6 statuts (prospect, en installation, actif, sous maintenance, suspendu, inactif), coordonnées GPS, lien WhatsApp, historique complet |
| **Accès techniques** | MikroTik, Starlink, Wi-Fi/SSID, routeurs, serveurs : IP/port, identifiant, mot de passe, n° de kit, abonnement, date de renouvellement, notes sécurisées. **Mots de passe chiffrés en base** (AES-256 via `APP_KEY`) et chargés uniquement au clic (jamais présents dans le HTML), consultations journalisées |
| **Interventions** | Installations, dépannages, maintenances, configurations, avec planning, rapport et facturation en un clic |
| **Devis** | Lignes produits/services, remise, TVA, suivi (brouillon → envoyé → accepté/refusé), duplication, impression PDF, envoi WhatsApp, **conversion en facture** |
| **Factures** | Ventes, prestations ou mixtes ; paiement à la création ou plus tard (paiements partiels, Orange Money, MTN MoMo, espèces…), annulation, impression PDF avec montant en lettres, relance WhatsApp |
| **Produits & services** | Catalogue matériel (stock, seuil d'alerte, prix d'achat/vente, marge) et prestations |
| **Fournisseurs & achats** | Achats fournisseurs qui alimentent le stock et mettent à jour le prix d'achat, paiements fournisseurs |
| **Dettes** | Créances clients (avec ancienneté 0-30 / 31-60 / 61-90 / 90+ jours) et dettes fournisseurs, position nette |
| **Dépenses** | Transport, carburant, outillage, crédit/internet, loyer, salaires, abonnements… avec répartition par catégorie |
| **Trésorerie** | Journal des entrées/sorties par période et solde par mode de paiement (caisse, Orange Money, MoMo…) |
| **Paramètres** | Infos entreprise (RCCM, NIF), devise, TVA, délais, mentions des devis/factures, compte utilisateur |

Le stock est géré automatiquement : **achat = entrée**, **facture = sortie**, annulation/suppression = correction.

## Installation

Prérequis : PHP 8.2+ (extensions `pdo_mysql`, `mbstring`, `intl` recommandée pour le montant en lettres), Composer, Node 20+, MySQL 8 / MariaDB 10.6+.

```bash
# 1. Dépendances
composer install
npm install

# 2. Configuration
cp .env.example .env
php artisan key:generate
# → éditez .env : DB_DATABASE, DB_USERNAME, DB_PASSWORD, ADMIN_EMAIL, ADMIN_PASSWORD

# 3. Base de données (créez d'abord la base "gc_manager" en utf8mb4)
php artisan migrate --seed                      # compte admin + paramètres
php artisan db:seed --class=DemoSeeder          # (optionnel) données de démonstration

# 4. Lancement
npm run build        # ou "npm run dev" pendant le développement
php artisan serve
```

Ouvrez http://localhost:8000 et connectez-vous avec `ADMIN_EMAIL` / `ADMIN_PASSWORD`
(par défaut `admin@gc-manager.local` / `password` — **changez-le dans Paramètres**).

## ⚠ Important : la clé APP_KEY

Les mots de passe MikroTik / Starlink sont chiffrés avec `APP_KEY`.
**Sauvegardez votre fichier `.env`** : si la clé est perdue ou régénérée, les accès enregistrés deviennent illisibles.
Ne lancez jamais `php artisan key:generate` sur une installation qui contient déjà des données.

## Mise en production (résumé)

```bash
APP_ENV=production  APP_DEBUG=false  APP_URL=https://votre-domaine
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
Servez le dossier `public/` en HTTPS (indispensable vu les accès stockés).

## Structure

```
app/Models                  Client, ClientAccess, Product, Supplier, Quote, Invoice, DocumentItem,
                            InvoicePayment, Purchase, PurchaseItem, PurchasePayment, Expense, Intervention, Setting
app/Models/Concerns         HasNumber (DEV-2026-0001, FAC-…, ACH-…), HasLineItems (lignes + totaux)
app/Http/Controllers        Un contrôleur par module
app/Support/Options.php     Modes de paiement, catégories de dépenses, types d'accès (modifiables)
resources/views             Blade + composants (x-badge, x-input, x-stat…) ; print/document = devis/facture A4
resources/js/app.js         Éditeur de lignes, révélation sécurisée des mots de passe (Alpine.js)
resources/css/app.css       Thème (couleur primaire #3d63dd)
```

Pour ajouter un mode de paiement (ex. Wave) ou une catégorie de dépense, modifiez `app/Support/Options.php`.
