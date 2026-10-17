<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoicePaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        abort_if($invoice->is_cancelled, 403, 'Facture annulée.');

        $data = $request->validate([
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:'.$invoice->balance],
            'method' => ['required', Rule::in(array_keys(Options::PAYMENT_METHODS))],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.max' => 'Le montant dépasse le reste à payer ('.money($invoice->balance).').',
        ]);

        $invoice->payments()->create($data);
        $invoice->refreshPaid();

        return back()->with('success', 'Paiement de '.money($data['amount']).' enregistré.');
    }

    public function destroy(InvoicePayment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();
        $invoice->refreshPaid();

        return back()->with('success', 'Paiement supprimé.');
    }
}
