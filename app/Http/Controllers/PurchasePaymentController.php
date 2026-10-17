<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchasePaymentController extends Controller
{
    public function store(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:'.$purchase->balance],
            'method' => ['required', Rule::in(array_keys(Options::PAYMENT_METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.max' => 'Le montant dépasse le reste dû ('.money($purchase->balance).').',
        ]);

        $purchase->payments()->create($data);
        $purchase->refreshPaid();

        return back()->with('success', 'Paiement fournisseur de '.money($data['amount']).' enregistré.');
    }

    public function destroy(PurchasePayment $payment)
    {
        $purchase = $payment->purchase;
        $payment->delete();
        $purchase->refreshPaid();

        return back()->with('success', 'Paiement supprimé.');
    }
}
