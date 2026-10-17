<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Intervention;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InterventionController extends Controller
{
    public function index(Request $request)
    {
        $interventions = Intervention::with('client')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('title', 'like', "%$t%")
                ->orWhereHas('client', fn ($c) => $c->search($t))))
            ->orderByRaw("CASE status WHEN 'en_cours' THEN 0 WHEN 'planifiee' THEN 1 ELSE 2 END")
            ->orderByDesc('scheduled_at')
            ->paginate(20)->withQueryString();

        return view('interventions.index', compact('interventions'));
    }

    public function create(Request $request)
    {
        return view('interventions.form', [
            'intervention' => new Intervention([
                'client_id' => $request->client_id,
                'status' => 'planifiee',
                'type' => 'installation',
                'scheduled_at' => now()->addDay()->setTime(9, 0),
            ]),
            'clients' => Client::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $intervention = Intervention::create($this->validated($request));

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention enregistrée.');
    }

    public function show(Intervention $intervention)
    {
        $intervention->load('client', 'invoice');

        return view('interventions.show', compact('intervention'));
    }

    public function edit(Intervention $intervention)
    {
        return view('interventions.form', [
            'intervention' => $intervention,
            'clients' => Client::withTrashed()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, Intervention $intervention)
    {
        $intervention->update($this->validated($request));

        return redirect()->route('interventions.show', $intervention)->with('success', 'Intervention mise à jour.');
    }

    public function destroy(Intervention $intervention)
    {
        $intervention->delete();

        return redirect()->route('interventions.index')->with('success', 'Intervention supprimée.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'type' => ['required', Rule::in(array_keys(Intervention::TYPES))],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(Intervention::STATUSES))],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'report' => ['nullable', 'string', 'max:10000'],
        ]);
        $data['amount'] = $data['amount'] ?? 0;
        $data['done_at'] = $data['status'] === 'terminee' ? now() : null;

        return $data;
    }
}
