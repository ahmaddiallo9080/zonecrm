<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->q);
        if ($q === '') {
            return redirect()->route('dashboard');
        }

        $results = [
            'Clients' => Client::search($q)->limit(8)->get()->map(fn ($c) => [route('clients.show', $c), $c->name, $c->code.' · '.$c->phone, 'users']),
            'Factures' => Invoice::with('client')->where('number', 'like', "%$q%")->orWhereHas('client', fn ($w) => $w->search($q))->latest()->limit(8)->get()
                ->map(fn ($i) => [route('invoices.show', $i), $i->number, $i->client->name.' · '.money($i->total), 'receipt']),
            'Devis' => Quote::with('client')->where('number', 'like', "%$q%")->orWhere('subject', 'like', "%$q%")->latest()->limit(8)->get()
                ->map(fn ($d) => [route('quotes.show', $d), $d->number, $d->client->name.' · '.money($d->total), 'doc']),
            'Produits' => Product::where('name', 'like', "%$q%")->orWhere('reference', 'like', "%$q%")->limit(8)->get()
                ->map(fn ($p) => [route('products.show', $p), $p->name, money($p->sale_price), 'box']),
            'Fournisseurs' => Supplier::where('name', 'like', "%$q%")->orWhere('phone', 'like', "%$q%")->limit(8)->get()
                ->map(fn ($s) => [route('suppliers.show', $s), $s->name, $s->phone, 'truck']),
        ];

        $results = array_filter($results, fn ($r) => $r->isNotEmpty());

        return view('search', compact('q', 'results'));
    }
}
