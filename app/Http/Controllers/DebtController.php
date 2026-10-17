<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

/**
 * Vue consolidée des dettes :
 *  - créances clients (factures non soldées)
 *  - dettes fournisseurs (achats non soldés)
 */
class DebtController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'clients');

        $clientDebts = Client::withTrashed()
            ->whereHas('invoices', fn ($q) => $q->unpaid())
            ->with(['invoices' => fn ($q) => $q->unpaid()->orderBy('date')])
            ->get()
            ->map(function ($c) {
                $c->due = $c->invoices->sum(fn ($i) => $i->balance);
                $c->oldest = $c->invoices->min('date');
                $c->overdue = $c->invoices->filter(fn ($i) => $i->due_date && $i->due_date->lt(today()))->sum(fn ($i) => $i->balance);

                return $c;
            })
            ->sortByDesc('due')->values();

        $supplierDebts = Supplier::withTrashed()
            ->whereHas('purchases', fn ($q) => $q->unpaid())
            ->with(['purchases' => fn ($q) => $q->unpaid()->orderBy('date')])
            ->get()
            ->map(function ($s) {
                $s->due = $s->purchases->sum(fn ($p) => $p->balance);
                $s->oldest = $s->purchases->min('date');
                $s->overdue = $s->purchases->filter(fn ($p) => $p->due_date && $p->due_date->lt(today()))->sum(fn ($p) => $p->balance);

                return $s;
            })
            ->sortByDesc('due')->values();

        // Ancienneté des créances clients
        $aging = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        foreach (Invoice::unpaid()->get() as $inv) {
            $days = $inv->date->diffInDays(today());
            $bucket = $days <= 30 ? '0-30' : ($days <= 60 ? '31-60' : ($days <= 90 ? '61-90' : '90+'));
            $aging[$bucket] += $inv->balance;
        }

        return view('debts.index', [
            'tab' => $tab,
            'clientDebts' => $clientDebts,
            'supplierDebts' => $supplierDebts,
            'aging' => $aging,
            'totalClients' => $clientDebts->sum('due'),
            'totalSuppliers' => $supplierDebts->sum('due'),
        ]);
    }
}
