<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAccess;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ClientAccessController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $client->accesses()->create($this->validated($request));

        return redirect()->to(route('clients.show', $client).'#acces')->with('success', 'Accès enregistré (chiffré).');
    }

    public function update(Request $request, ClientAccess $access)
    {
        $data = $this->validated($request);

        // Champ vide = on conserve le secret existant
        foreach (['password', 'extra'] as $secret) {
            if (blank($data[$secret] ?? null) && ! $request->boolean("clear_$secret")) {
                unset($data[$secret]);
            }
        }

        $access->update($data);

        return redirect()->to(route('clients.show', $access->client_id).'#acces')->with('success', 'Accès mis à jour.');
    }

    public function destroy(ClientAccess $access)
    {
        $clientId = $access->client_id;
        $access->delete();

        return redirect()->to(route('clients.show', $clientId).'#acces')->with('success', 'Accès supprimé.');
    }

    /** Renvoie le mot de passe déchiffré à la demande (jamais rendu dans le HTML). */
    public function reveal(Request $request, ClientAccess $access)
    {
        Log::info('Consultation accès client', [
            'user' => $request->user()->id, 'access' => $access->id, 'ip' => $request->ip(),
        ]);

        return response()->json([
            'password' => $access->password,
            'extra' => $access->extra,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(array_keys(Options::ACCESS_TYPES))],
            'label' => ['required', 'string', 'max:150'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:150'],
            'password' => ['nullable', 'string', 'max:500'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'plan' => ['nullable', 'string', 'max:150'],
            'renewal_date' => ['nullable', 'date'],
            'extra' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
