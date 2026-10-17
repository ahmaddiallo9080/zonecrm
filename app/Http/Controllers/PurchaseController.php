<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesLines;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    use ValidatesLines;

    public function index(Request $request)
    {
        $purchases = Purchase::with('supplier')
            ->when($request->supplier_id, fn ($q, $s) => $q->where('supplier_id', $s))
            ->when($request->status === 'unpaid', fn ($q) => $q->unpaid())
            ->when($request->status === 'paid', fn ($q) => $q->whereColumn('amount_paid', '>=', 'total'))
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('number', 'like', "%$t%")->orWhere('supplier_ref', 'like', "%$t%")))
            ->latest('date')->latest('id')
            ->paginate(20)->withQueryString();

        $summary = Purchase::selectRaw('COALESCE(SUM(total),0) as total, COALESCE(SUM(amount_paid),0) as paid')->first();

        return view('purchases.index', [
            'purchases' => $purchases,
            'summary' => $summary,
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(Request $request)
    {
        return view('purchases.form', [
            'purchase' => new Purchase(['supplier_id' => $request->supplier_id, 'date' => today()]),
            'items' => [],
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'products' => $this->productOptions()->where('type', 'produit')->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $lines = $this->validateLines($request, 'unit_cost');
        $payment = $request->validate([
            'paid_now' => ['nullable', 'numeric', 'min:0'],
            'paid_method' => ['nullable', Rule::in(array_keys(Options::PAYMENT_METHODS))],
        ]);

        $purchase = DB::transaction(function () use ($data, $lines, $payment) {
            $purchase = Purchase::create($data + ['number' => Purchase::nextNumber()]);
            $purchase->syncItems($lines);

            if (($payment['paid_now'] ?? 0) > 0) {
                $purchase->payments()->create([
                    'date' => $purchase->date,
                    'amount' => min($payment['paid_now'], $purchase->total),
                    'method' => $payment['paid_method'] ?? 'especes',
                    'note' => 'Paiement à l\'achat',
                ]);
                $purchase->refreshPaid();
            }

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)->with('success', "Achat {$purchase->number} enregistré, stock mis à jour.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'items.product', 'payments');

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        return view('purchases.form', [
            'purchase' => $purchase,
            'items' => $purchase->items->map->only('product_id', 'description', 'quantity', 'unit_cost')->all(),
            'suppliers' => Supplier::withTrashed()->orderBy('name')->pluck('name', 'id'),
            'products' => $this->productOptions()->where('type', 'produit')->values(),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $this->validated($request);
        $lines = $this->validateLines($request, 'unit_cost');

        try {
            DB::transaction(function () use ($purchase, $data, $lines) {
                $purchase->update($data);
                $purchase->syncItems($lines);
                if ($purchase->total < $purchase->amount_paid) {
                    throw new \RuntimeException('Le total est inférieur aux paiements déjà effectués.');
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Achat mis à jour.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->payments()->exists()) {
            return back()->with('error', 'Cet achat a des paiements enregistrés : supprimez-les d\'abord.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items()->with('product')->get() as $item) {
                $item->product?->moveStock(-$item->quantity);
            }
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Achat supprimé, stock corrigé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'supplier_ref' => ['nullable', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
