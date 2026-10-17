<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quote;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Jeu de données de démonstration réaliste (Conakry).
 * php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        // Fournisseurs
        $s1 = Supplier::create(['name' => 'Net Distribution Guinée', 'contact_person' => 'M. Camara', 'phone' => '+224 620 00 00 01', 'country' => 'Guinée', 'address' => 'Madina, Conakry']);
        $s2 = Supplier::create(['name' => 'Dakar Network Supply', 'contact_person' => 'Awa Ndiaye', 'phone' => '+221 77 000 00 02', 'country' => 'Sénégal']);

        // Catalogue
        $p = fn ($a) => Product::create($a + ['type' => 'produit', 'unit' => 'pièce', 'is_active' => true]);
        $hap = $p(['name' => 'MikroTik hAP ax²', 'reference' => 'MT-HAPAX2', 'category' => 'Routeurs MikroTik', 'purchase_price' => 850000, 'sale_price' => 1150000, 'stock_qty' => 0, 'alert_qty' => 2, 'supplier_id' => $s1->id]);
        $rb = $p(['name' => 'MikroTik hEX RB750Gr3', 'reference' => 'MT-HEX', 'category' => 'Routeurs MikroTik', 'purchase_price' => 520000, 'sale_price' => 750000, 'stock_qty' => 0, 'alert_qty' => 2, 'supplier_id' => $s1->id]);
        $sl = $p(['name' => 'Kit Starlink Standard', 'reference' => 'SL-STD', 'category' => 'Kits Starlink', 'unit' => 'kit', 'purchase_price' => 3800000, 'sale_price' => 4500000, 'stock_qty' => 0, 'alert_qty' => 1, 'supplier_id' => $s2->id]);
        $ap = $p(['name' => 'Ubiquiti NanoStation M5', 'reference' => 'UBNT-NSM5', 'category' => 'Points d\'accès / Antennes', 'purchase_price' => 650000, 'sale_price' => 900000, 'stock_qty' => 0, 'alert_qty' => 3, 'supplier_id' => $s2->id]);
        $cab = $p(['name' => 'Câble FTP Cat6 extérieur', 'reference' => 'CAB-CAT6', 'category' => 'Câbles & connectique', 'unit' => 'mètre', 'purchase_price' => 4000, 'sale_price' => 7000, 'stock_qty' => 0, 'alert_qty' => 50, 'supplier_id' => $s1->id]);
        $s = fn ($a) => Product::create($a + ['type' => 'service', 'unit' => 'forfait', 'is_active' => true, 'category' => 'Services']);
        $inst = $s(['name' => 'Installation & configuration hotspot', 'reference' => 'SRV-INST', 'sale_price' => 1500000]);
        $dep = $s(['name' => 'Dépannage sur site', 'reference' => 'SRV-DEP', 'sale_price' => 300000]);
        $conf = $s(['name' => 'Configuration MikroTik à distance', 'reference' => 'SRV-CONF', 'sale_price' => 200000]);

        // Achats (alimentent le stock)
        $pu1 = Purchase::create(['number' => Purchase::nextNumber(), 'supplier_id' => $s1->id, 'supplier_ref' => 'NDG-4471', 'date' => now()->subDays(40), 'due_date' => now()->subDays(10)]);
        $pu1->syncItems([
            ['product_id' => $hap->id, 'description' => $hap->name, 'quantity' => 5, 'unit_cost' => 850000],
            ['product_id' => $rb->id, 'description' => $rb->name, 'quantity' => 4, 'unit_cost' => 520000],
            ['product_id' => $cab->id, 'description' => $cab->name, 'quantity' => 300, 'unit_cost' => 4000],
        ]);
        $pu1->payments()->create(['date' => now()->subDays(40), 'amount' => 4000000, 'method' => 'virement']);
        $pu1->refreshPaid();

        $pu2 = Purchase::create(['number' => Purchase::nextNumber(), 'supplier_id' => $s2->id, 'date' => now()->subDays(20), 'due_date' => now()->addDays(10)]);
        $pu2->syncItems([
            ['product_id' => $sl->id, 'description' => $sl->name, 'quantity' => 3, 'unit_cost' => 3800000],
            ['product_id' => $ap->id, 'description' => $ap->name, 'quantity' => 6, 'unit_cost' => 650000],
        ]);
        $pu2->payments()->create(['date' => now()->subDays(20), 'amount' => 10000000, 'method' => 'orange_money']);
        $pu2->refreshPaid();

        // Clients
        $c1 = Client::create(['type' => 'entreprise', 'name' => 'Mamadou Diallo', 'company' => 'Kaloum Wi-Fi Zone', 'phone' => '+224 621 11 22 33', 'city' => 'Conakry', 'address' => 'Kaloum, Almamya', 'location' => '9.5092, -13.7122', 'status' => 'actif', 'notes' => '3 antennes sur le toit, routeur dans le local technique.']);
        $c2 = Client::create(['type' => 'particulier', 'name' => 'Fatoumata Bah', 'phone' => '+224 622 44 55 66', 'city' => 'Conakry', 'address' => 'Ratoma, Kipé', 'status' => 'en_installation']);
        $c3 = Client::create(['type' => 'entreprise', 'name' => 'Ibrahima Sylla', 'company' => 'Hôtel Le Palmier', 'phone' => '+224 623 77 88 99', 'city' => 'Kindia', 'status' => 'maintenance']);
        Client::create(['type' => 'particulier', 'name' => 'Aïssatou Condé', 'phone' => '+224 624 00 11 22', 'city' => 'Coyah', 'status' => 'prospect']);

        // Accès techniques (mots de passe chiffrés)
        $c1->accesses()->create(['type' => 'mikrotik', 'label' => 'Routeur principal hAP ax²', 'host' => '192.168.88.1', 'port' => 8291, 'username' => 'admin', 'password' => 'Kz!2026-wifi', 'serial_number' => 'HE308XXXXXX', 'plan' => 'RouterOS 7.16', 'extra' => "User hotspot test : demo / demo123\nProfil 1 jour : 1000 GNF"]);
        $c1->accesses()->create(['type' => 'starlink', 'label' => 'Kit Starlink du toit', 'host' => 'https://www.starlink.com', 'username' => 'kaloum.wifi@gmail.com', 'password' => 'StarL1nk#Kal', 'serial_number' => 'KIT-2026-00123', 'plan' => 'Standard – Résidentiel', 'renewal_date' => now()->addDays(5)]);
        $c1->accesses()->create(['type' => 'wifi', 'label' => 'Réseau clients', 'username' => 'KALOUM-WIFI-ZONE', 'password' => '']);
        $c3->accesses()->create(['type' => 'mikrotik', 'label' => 'Routeur réception', 'host' => '10.10.0.1', 'port' => 8291, 'username' => 'admin', 'password' => 'Palm13r!', 'plan' => 'RouterOS 7.14']);

        // Devis
        $q1 = Quote::create(['number' => Quote::nextNumber(), 'client_id' => $c2->id, 'date' => now()->subDays(3), 'valid_until' => now()->addDays(12), 'status' => 'envoye', 'subject' => 'Installation Wi-Fi Zone résidence Kipé', 'discount' => 100000, 'tax_rate' => 0, 'notes' => 'Devis valable 15 jours. Acompte de 50 % à la commande.']);
        $q1->syncItems([
            ['product_id' => $sl->id, 'description' => $sl->name, 'quantity' => 1, 'unit_price' => 4500000],
            ['product_id' => $hap->id, 'description' => $hap->name, 'quantity' => 1, 'unit_price' => 1150000],
            ['product_id' => $cab->id, 'description' => $cab->name, 'quantity' => 40, 'unit_price' => 7000],
            ['product_id' => $inst->id, 'description' => $inst->name, 'quantity' => 1, 'unit_price' => 1500000],
        ]);

        // Factures
        $i1 = Invoice::create(['number' => Invoice::nextNumber(), 'client_id' => $c1->id, 'type' => 'mixte', 'date' => now()->subDays(25), 'due_date' => now()->subDays(18), 'subject' => 'Installation hotspot 3 antennes']);
        $i1->syncItems([
            ['product_id' => $hap->id, 'description' => $hap->name, 'quantity' => 1, 'unit_price' => 1150000],
            ['product_id' => $ap->id, 'description' => $ap->name, 'quantity' => 3, 'unit_price' => 900000],
            ['product_id' => $cab->id, 'description' => $cab->name, 'quantity' => 80, 'unit_price' => 7000],
            ['product_id' => $inst->id, 'description' => $inst->name, 'quantity' => 1, 'unit_price' => 1500000],
        ]);
        $i1->applyStock(-1);
        $i1->payments()->create(['date' => now()->subDays(25), 'amount' => 3000000, 'method' => 'especes']);
        $i1->payments()->create(['date' => now()->subDays(5), 'amount' => 1500000, 'method' => 'orange_money', 'reference' => 'OM-889921']);
        $i1->refreshPaid();

        $i2 = Invoice::create(['number' => Invoice::nextNumber(), 'client_id' => $c3->id, 'type' => 'prestation', 'date' => now()->subDays(2), 'due_date' => now()->addDays(5), 'subject' => 'Dépannage + reconfiguration']);
        $i2->syncItems([
            ['product_id' => $dep->id, 'description' => $dep->name, 'quantity' => 1, 'unit_price' => 300000],
            ['product_id' => $conf->id, 'description' => $conf->name, 'quantity' => 1, 'unit_price' => 200000],
        ]);
        $i2->payments()->create(['date' => now()->subDays(2), 'amount' => 500000, 'method' => 'mtn_momo']);
        $i2->refreshPaid();

        // Interventions
        Intervention::create(['client_id' => $c2->id, 'type' => 'installation', 'title' => 'Pose kit Starlink + routeur', 'scheduled_at' => now()->addDays(2)->setTime(9, 0), 'status' => 'planifiee', 'amount' => 1500000]);
        Intervention::create(['client_id' => $c1->id, 'type' => 'maintenance', 'title' => 'Contrôle mensuel des antennes', 'scheduled_at' => now()->addDays(6)->setTime(14, 30), 'status' => 'planifiee']);
        Intervention::create(['client_id' => $c3->id, 'type' => 'depannage', 'title' => 'Coupure réseau chambres 2e étage', 'scheduled_at' => now()->subDays(2)->setTime(10, 0), 'status' => 'terminee', 'done_at' => now()->subDays(2), 'amount' => 300000, 'invoice_id' => $i2->id, 'report' => 'Câble endommagé remplacé, AP reconfiguré.']);

        // Dépenses
        foreach ([
            [5, 'carburant', 'Carburant moto – chantier Kaloum', 150000, 'especes'],
            [12, 'communication', 'Recharge internet forfait mensuel', 250000, 'orange_money'],
            [18, 'outillage', 'Pince à sertir + connecteurs RJ45', 180000, 'especes'],
            [35, 'transport', 'Taxi Kindia aller-retour', 200000, 'especes'],
            [65, 'loyer', 'Loyer atelier', 1500000, 'virement'],
            [95, 'carburant', 'Carburant', 120000, 'especes'],
        ] as [$days, $cat, $desc, $amount, $method]) {
            Expense::create(['date' => now()->subDays($days), 'category' => $cat, 'description' => $desc, 'amount' => $amount, 'method' => $method]);
        }
    }
}
