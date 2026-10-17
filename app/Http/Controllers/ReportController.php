<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Support\Options;
use Illuminate\Http\Request;

/**
 * Trésorerie : journal des entrées / sorties sur une période,
 * ventilé par mode de paiement (caisse, Orange Money, MoMo…).
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = ($request->date('from') ?? now()->startOfMonth())->startOfDay();
        $to = ($request->date('to') ?? now()->endOfMonth())->endOfDay();
        $range = [$from, $to];

        $in = InvoicePayment::with('invoice.client')->whereBetween('date', $range)->get();
        $outSuppliers = PurchasePayment::with('purchase.supplier')->whereBetween('date', $range)->get();
        $outExpenses = Expense::whereBetween('date', $range)->get();

        // Journal unifié
        $journal = collect()
            ->merge($in->map(fn ($p) => ['date' => $p->date, 'label' => 'Encaissement '.$p->invoice?->number.' — '.$p->invoice?->client?->name, 'method' => $p->method, 'in' => $p->amount, 'out' => 0, 'url' => route('invoices.show', $p->invoice_id)]))
            ->merge($outSuppliers->map(fn ($p) => ['date' => $p->date, 'label' => 'Paiement fournisseur '.$p->purchase?->number.' — '.$p->purchase?->supplier?->name, 'method' => $p->method, 'in' => 0, 'out' => $p->amount, 'url' => route('purchases.show', $p->purchase_id)]))
            ->merge($outExpenses->map(fn ($e) => ['date' => $e->date, 'label' => $e->category_label.' — '.$e->description, 'method' => $e->method, 'in' => 0, 'out' => $e->amount, 'url' => route('expenses.edit', $e)]))
            ->sortByDesc(fn ($r) => $r['date']->format('Ymd'))->values();

        // Par mode de paiement
        $byMethod = [];
        foreach (array_keys(Options::PAYMENT_METHODS) as $m) {
            $byMethod[$m] = [
                'in' => $journal->where('method', $m)->sum('in'),
                'out' => $journal->where('method', $m)->sum('out'),
            ];
        }
        $byMethod = array_filter($byMethod, fn ($v) => $v['in'] || $v['out']);

        $totals = [
            'invoiced' => Invoice::where('is_cancelled', false)->whereBetween('date', $range)->sum('total'),
            'purchased' => Purchase::whereBetween('date', $range)->sum('total'),
            'in' => $journal->sum('in'),
            'out_suppliers' => $outSuppliers->sum('amount'),
            'out_expenses' => $outExpenses->sum('amount'),
        ];
        $totals['out'] = $totals['out_suppliers'] + $totals['out_expenses'];
        $totals['net'] = $totals['in'] - $totals['out'];

        return view('reports.index', compact('journal', 'byMethod', 'totals', 'from', 'to'));
    }
}
