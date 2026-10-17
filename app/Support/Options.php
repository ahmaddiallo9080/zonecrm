<?php

namespace App\Support;

/**
 * Listes de valeurs partagées par les modules (modes de paiement, catégories...).
 * Chaque entrée : clé => [libellé, couleur du badge].
 */
class Options
{
    public const PAYMENT_METHODS = [
        'especes' => 'Espèces',
        'orange_money' => 'Orange Money',
        'mtn_momo' => 'MTN Mobile Money',
        'virement' => 'Virement bancaire',
        'cheque' => 'Chèque',
        'autre' => 'Autre',
    ];

    public const EXPENSE_CATEGORIES = [
        'transport' => 'Transport / Déplacement',
        'carburant' => 'Carburant',
        'outillage' => 'Outillage & consommables',
        'communication' => 'Crédit / Internet / Communication',
        'loyer' => 'Loyer & charges',
        'salaire' => 'Salaires & main d\'œuvre',
        'abonnement' => 'Abonnements (Starlink, licences...)',
        'marketing' => 'Publicité / Marketing',
        'taxes' => 'Impôts & taxes',
        'autre' => 'Autre',
    ];

    public const PRODUCT_CATEGORIES = [
        'Routeurs MikroTik',
        'Kits Starlink',
        'Points d\'accès / Antennes',
        'Switchs',
        'Câbles & connectique',
        'Énergie (onduleurs, solaire)',
        'Accessoires',
        'Services',
    ];

    public const ACCESS_TYPES = [
        'mikrotik' => ['MikroTik', 'blue'],
        'starlink' => ['Starlink', 'slate'],
        'wifi' => ['Wi-Fi / SSID', 'emerald'],
        'routeur' => ['Routeur / AP', 'violet'],
        'serveur' => ['Serveur / Hotspot', 'amber'],
        'autre' => ['Autre', 'gray'],
    ];

    public static function label(array $list, ?string $key): string
    {
        $value = $list[$key] ?? $key ?? '—';

        return is_array($value) ? $value[0] : $value;
    }
}
