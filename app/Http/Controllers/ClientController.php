<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->search($request->q)
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->withCount('accesses')
            ->withSum(['invoices as due' => fn ($q) => $q->where('is_cancelled', false)], \DB::raw('total - amount_paid'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $counts = Client::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('clients.index', compact('clients', 'counts'));
    }

    public function create()
    {
        return view('clients.form', ['client' => new Client(['status' => 'prospect', 'type' => 'particulier', 'city' => 'Conakry'])]);
    }

    public function store(Request $request)
    {
        $client = Client::create($this->validated($request));

        return redirect()->route('clients.show', $client)->with('success', 'Client créé. Vous pouvez maintenant ajouter ses accès.');
    }

    public function show(Client $client)
    {
        $client->load(['accesses', 'interventions', 'quotes', 'invoices', 'payments.invoice']);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.form', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $client->update($this->validated($request));

        return redirect()->route('clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        if ($client->balance > 0) {
            return back()->with('error', 'Impossible d\'archiver un client qui a encore une dette ('.money($client->balance).').');
        }
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client archivé.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['particulier', 'entreprise'])],
            'name' => ['required', 'string', 'max:150'],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'phone2' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(Client::STATUSES))],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
