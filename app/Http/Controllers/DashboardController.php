<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAccess;
use App\Models\Expense;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Quote;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $income = InvoicePayment::whereBetween('date', [$start, $end])->sum('amount');
        $expenses = Expense::whereBetween('date', [$start, $end])->sum('amount');
        $supplierPaid = PurchasePayment::whereBetween('date', [$start, $end])->sum('amount');
        $sales = Invoice::where('is_cancelled', false)->whereBetween('date', [$start, $end])->sum('total');

        $stats = [
            'income' => $income,
            'outflow' => $expenses + $supplierPaid,
            'net' => $income - $expenses - $supplierPaid,
            'sales' => $sales,
            'receivables' => Invoice::unpaid()->selectRaw('COALESCE(SUM(total - amount_paid),0) as v')->value('v'),
            'payables' => Purchase::unpaid()->selectRaw('COALESCE(SUM(total - amount_paid),0) as v')->value('v'),
            'clients_active' => Client::where('status', 'actif')->count(),
            'clients_total' => Client::count(),
            'quotes_pending' => Quote::whereIn('status', ['brouillon', 'envoye'])->count(),
        ];

        // Graphique 6 derniers mois
        $chart = ['labels' => [], 'income' => [], 'outflow' => []];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $range = [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()];
            $chart['labels'][] = ucfirst($m->locale('fr')->translatedFormat('M Y'));
            $chart['income'][] = (float) InvoicePayment::whereBetween('date', $range)->sum('amount');
            $chart['outflow'][] = (float) Expense::whereBetween('date', $range)->sum('amount')
                + (float) PurchasePayment::whereBetween('date', $range)->sum('amount');
        }

        return view('dashboard', [
            'stats' => $stats,
            'chart' => $chart,
            'upcoming' => Intervention::with('client')->whereIn('status', ['planifiee', 'en_cours'])
                ->orderBy('scheduled_at')->limit(6)->get(),
            'overdue' => Invoice::with('client')->unpaid()->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())->orderBy('due_date')->limit(6)->get(),
            'lowStock' => Product::lowStock()->where('is_active', true)->orderBy('stock_qty')->limit(6)->get(),
            'renewals' => ClientAccess::with('client')->whereNotNull('renewal_date')
                ->whereDate('renewal_date', '<=', today()->addDays(15))->orderBy('renewal_date')->limit(6)->get(),
            'recentInvoices' => Invoice::with('client')->latest()->limit(5)->get(),
        ]);
    }
}
