<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesLines;
use App\Models\Client;
use App\Models\Intervention;
use App\Models\Invoice;
use App\Models\Setting;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    use ValidatesLines;

    public function index(Request $request)
    {
        $invoices = Invoice::with('client')
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('number', 'like', "%$t%")
                ->orWhere('subject', 'like', "%$t%")
                ->orWhereHas('client', fn ($c) => $c->search($t))))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->status, function ($q, $s) {
                match ($s) {
                    'payee' => $q->where('is_cancelled', false)->whereColumn('amount_paid', '>=', 'total'),
                    'partielle' => $q->unpaid()->where('amount_paid', '>', 0),
                    'impayee' => $q->unpaid()->where('amount_paid', 0),
                    'en_retard' => $q->unpaid()->whereDate('due_date', '<', today()),
                    'annulee' => $q->where('is_cancelled', true),
                    default => null,
                };
            })
            ->when($request->from, fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->latest('date')->latest('id')
            ->paginate(20)->withQueryString();

        $summary = Invoice::where('is_cancelled', false)
            ->selectRaw('COALESCE(SUM(total),0) as total, COALESCE(SUM(amount_paid),0) as paid')->first();

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function create(Request $request)
    {
        $items = [];
        $intervention = null;
        if ($request->intervention_id) {
            $intervention = Intervention::find($request->intervention_id);
            if ($intervention) {
                $items[] = ['product_id' => null, 'description' => $intervention->type_label.' – '.$intervention->title, 'quantity' => 1, 'unit_price' => $intervention->amount];
            }
        }

        $invoice = new Invoice([
            'client_id' => $request->client_id ?? $intervention?->client_id,
            'type' => $intervention ? 'prestation' : 'vente',
            'date' => today(),
            'due_date' => today()->addDays((int) Setting::get('invoice_due_days', 7)),
            'tax_rate' => Setting::get('default_tax_rate', 0),
            'discount' => 0,
            'subject' => $intervention?->title,
            'notes' => Setting::get('invoice_footer'),
        ]);

        return view('invoices.form', $this->formData($invoice, $items) + ['intervention' => $intervention]);
    }

    public function store(Request $request)
    {
        [$data, $lines] = $this->validated($request);
        $payment = $request->validate([
            'paid_now' => ['nullable', 'numeric', 'min:0'],
            'paid_method' => ['nullable', Rule::in(array_keys(Options::PAYMENT_METHODS))],
        ]);

        $invoice = DB::transaction(function () use ($data, $lines, $payment, $request) {
            $invoice = Invoice::create($data + ['number' => Invoice::nextNumber()]);
            $invoice->syncItems($lines);
            $invoice->applyStock(-1);

            if (($payment['paid_now'] ?? 0) > 0) {
                $invoice->payments()->create([
                    'date' => $invoice->date,
                    'amount' => min($payment['paid_now'], $invoice->total),
                    'method' => $payment['paid_method'] ?? 'especes',
                    'note' => 'Paiement à la facturation',
                ]);
                $invoice->refreshPaid();
            }

            if ($request->intervention_id) {
                Intervention::whereKey($request->intervention_id)->update(['invoice_id' => $invoice->id]);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', "Facture {$invoice->number} créée.".$this->stockWarning($invoice));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('client', 'items.product', 'payments', 'quote');

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->is_cancelled) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Une facture annulée ne peut pas être modifiée.');
        }

        return view('invoices.form', $this->formData($invoice, $invoice->items->map->only('product_id', 'description', 'quantity', 'unit_price')->all()));
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_if($invoice->is_cancelled, 403);
        [$data, $lines] = $this->validated($request);

        try {
            DB::transaction(function () use ($invoice, $data, $lines) {
                $invoice->applyStock(+1); // on remet l'ancien matériel en stock
                $invoice->update($data);
                $invoice->syncItems($lines);
                $invoice->applyStock(-1);

                if ($invoice->total < $invoice->amount_paid) {
                    throw new \RuntimeException('Le nouveau total ('.money($invoice->total).') est inférieur aux paiements déjà reçus ('.money($invoice->amount_paid).').');
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Facture mise à jour.'.$this->stockWarning($invoice->fresh()));
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Supprimez d\'abord les paiements enregistrés avant d\'annuler la facture.');
        }

        DB::transaction(function () use ($invoice) {
            if (! $invoice->is_cancelled) {
                $invoice->applyStock(+1);
                $invoice->forceFill(['is_cancelled' => true])->save();
                $invoice->quote?->update(['status' => 'accepte']);
            }
        });

        return back()->with('success', 'Facture annulée, le matériel a été remis en stock.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Cette facture a des paiements : supprimez-les ou annulez la facture.');
        }

        DB::transaction(function () use ($invoice) {
            if (! $invoice->is_cancelled) {
                $invoice->applyStock(+1);
            }
            $invoice->quote?->update(['status' => 'accepte']);
            Intervention::where('invoice_id', $invoice->id)->update(['invoice_id' => null]);
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('success', 'Facture supprimée.');
    }

    public function print(Invoice $invoice)
    {
        $invoice->load('client', 'items', 'payments');

        return view('print.document', ['doc' => $invoice, 'kind' => 'FACTURE']);
    }

    private function stockWarning(Invoice $invoice): string
    {
        $negative = $invoice->items()->with('product')->get()
            ->filter(fn ($i) => $i->product && ! $i->product->isService() && $i->product->fresh()->stock_qty < 0)
            ->map(fn ($i) => $i->product->name);

        return $negative->isNotEmpty() ? ' ⚠ Stock négatif : '.$negative->join(', ').' — pensez à saisir vos achats.' : '';
    }

    private function formData(Invoice $invoice, array $items): array
    {
        return [
            'invoice' => $invoice,
            'items' => $items,
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company'])->mapWithKeys(fn ($c) => [$c->id => $c->display_name]),
            'products' => $this->productOptions(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'type' => ['required', Rule::in(array_keys(Invoice::TYPES))],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $data['discount'] = $data['discount'] ?? 0;
        $data['tax_rate'] = $data['tax_rate'] ?? 0;

        return [$data, $this->validateLines($request)];
    }
}
