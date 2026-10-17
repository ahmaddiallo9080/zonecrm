<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Supplier;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();

        $base = Expense::whereBetween('date', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->when($request->category, fn ($q, $c) => $q->where('category', $c));

        $expenses = (clone $base)->with('supplier', 'client')->latest('date')->latest('id')->paginate(25)->withQueryString();
        $total = (clone $base)->sum('amount');
        $byCategory = (clone $base)->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->pluck('total', 'category');

        return view('expenses.index', compact('expenses', 'total', 'byCategory', 'from', 'to'));
    }

    public function create()
    {
        return view('expenses.form', $this->formData(new Expense(['date' => today(), 'method' => 'especes', 'category' => 'transport'])));
    }

    public function store(Request $request)
    {
        Expense::create($this->validated($request));

        return redirect()->route('expenses.index')->with('success', 'Dépense enregistrée.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.form', $this->formData($expense));
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validated($request));

        return redirect()->route('expenses.index')->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Dépense supprimée.');
    }

    private function formData(Expense $expense): array
    {
        return [
            'expense' => $expense,
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', Rule::in(array_keys(Options::EXPENSE_CATEGORIES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', Rule::in(array_keys(Options::PAYMENT_METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
        ]);
    }
}
