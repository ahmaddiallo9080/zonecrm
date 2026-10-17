<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%$t%")
                ->orWhere('contact_person', 'like', "%$t%")->orWhere('phone', 'like', "%$t%")))
            ->withCount('purchases')
            ->withSum('purchases as purchased', 'total')
            ->withSum('purchases as paid', 'amount_paid')
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.form', ['supplier' => new Supplier(['country' => 'Guinée'])]);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Fournisseur créé.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchases.payments', 'products']);
        $expenses = $supplier->expenses()->latest('date')->limit(10)->get();

        return view('suppliers.show', compact('supplier', 'expenses'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($this->validated($request));

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->balance > 0) {
            return back()->with('error', 'Vous devez encore '.money($supplier->balance).' à ce fournisseur.');
        }
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Fournisseur archivé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
