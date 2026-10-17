<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesLines;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    use ValidatesLines;

    public function index(Request $request)
    {
        $quotes = Quote::with('client')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('number', 'like', "%$t%")
                ->orWhere('subject', 'like', "%$t%")
                ->orWhereHas('client', fn ($c) => $c->search($t))))
            ->latest('date')->latest('id')
            ->paginate(20)->withQueryString();

        $totals = Quote::selectRaw('status, count(*) as c, sum(total) as t')->groupBy('status')->get()->keyBy('status');

        return view('quotes.index', compact('quotes', 'totals'));
    }

    public function create(Request $request)
    {
        $quote = new Quote([
            'client_id' => $request->client_id,
            'date' => today(),
            'valid_until' => today()->addDays((int) Setting::get('quote_validity_days', 15)),
            'status' => 'brouillon',
            'tax_rate' => Setting::get('default_tax_rate', 0),
            'discount' => 0,
            'notes' => Setting::get('quote_footer'),
        ]);

        return view('quotes.form', $this->formData($quote, []));
    }

    public function store(Request $request)
    {
        [$data, $lines] = $this->validated($request);

        $quote = DB::transaction(function () use ($data, $lines) {
            $quote = Quote::create($data + ['number' => Quote::nextNumber()]);
            $quote->syncItems($lines);

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)->with('success', "Devis {$quote->number} créé.");
    }

    public function show(Quote $quote)
    {
        $quote->load('client', 'items', 'invoice');

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        if ($quote->status === 'facture') {
            return redirect()->route('quotes.show', $quote)->with('error', 'Ce devis a déjà été facturé et ne peut plus être modifié.');
        }

        return view('quotes.form', $this->formData($quote, $quote->items->map->only('product_id', 'description', 'quantity', 'unit_price')->all()));
    }

    public function update(Request $request, Quote $quote)
    {
        abort_if($quote->status === 'facture', 403, 'Devis déjà facturé.');
        [$data, $lines] = $this->validated($request);

        DB::transaction(function () use ($quote, $data, $lines) {
            $quote->update($data);
            $quote->syncItems($lines);
        });

        return redirect()->route('quotes.show', $quote)->with('success', 'Devis mis à jour.');
    }

    public function destroy(Quote $quote)
    {
        if ($quote->invoice) {
            return back()->with('error', 'Ce devis est lié à une facture : supprimez d\'abord la facture.');
        }
        $quote->items()->delete();
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Devis supprimé.');
    }

    public function status(Request $request, Quote $quote)
    {
        $request->validate(['status' => ['required', Rule::in(['brouillon', 'envoye', 'accepte', 'refuse'])]]);
        abort_if($quote->status === 'facture', 403);
        $quote->update(['status' => $request->status]);

        return back()->with('success', 'Statut du devis : '.$quote->status_label.'.');
    }

    /** Transforme un devis accepté en facture (avec sortie de stock). */
    public function convert(Quote $quote)
    {
        if ($quote->invoice) {
            return redirect()->route('invoices.show', $quote->invoice);
        }

        $invoice = DB::transaction(function () use ($quote) {
            $hasProducts = $quote->items()->whereHas('product', fn ($p) => $p->where('type', 'produit'))->exists();
            $hasServices = $quote->items()->whereDoesntHave('product', fn ($p) => $p->where('type', 'produit'))->exists();

            $invoice = Invoice::create([
                'number' => Invoice::nextNumber(),
                'client_id' => $quote->client_id,
                'quote_id' => $quote->id,
                'type' => $hasProducts && $hasServices ? 'mixte' : ($hasProducts ? 'vente' : 'prestation'),
                'date' => today(),
                'due_date' => today()->addDays((int) Setting::get('invoice_due_days', 7)),
                'subject' => $quote->subject,
                'discount' => $quote->discount,
                'tax_rate' => $quote->tax_rate,
                'notes' => Setting::get('invoice_footer'),
            ]);
            $invoice->syncItems($quote->items->map->only('product_id', 'description', 'quantity', 'unit_price')->all());
            $invoice->applyStock(-1);

            $quote->update(['status' => 'facture']);
            $quote->client->update(['status' => $quote->client->status === 'prospect' ? 'en_installation' : $quote->client->status]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('success', "Facture {$invoice->number} générée depuis le devis {$quote->number}.");
    }

    public function duplicate(Quote $quote)
    {
        $copy = DB::transaction(function () use ($quote) {
            $copy = $quote->replicate(['number', 'status']);
            $copy->number = Quote::nextNumber();
            $copy->status = 'brouillon';
            $copy->date = today();
            $copy->valid_until = today()->addDays((int) Setting::get('quote_validity_days', 15));
            $copy->save();
            $copy->syncItems($quote->items->map->only('product_id', 'description', 'quantity', 'unit_price')->all());

            return $copy;
        });

        return redirect()->route('quotes.edit', $copy)->with('success', "Copie créée : {$copy->number}.");
    }

    public function print(Quote $quote)
    {
        $quote->load('client', 'items');

        return view('print.document', ['doc' => $quote, 'kind' => 'DEVIS']);
    }

    private function formData(Quote $quote, array $items): array
    {
        return [
            'quote' => $quote,
            'items' => $items,
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company'])->mapWithKeys(fn ($c) => [$c->id => $c->display_name]),
            'products' => $this->productOptions(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:date'],
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
