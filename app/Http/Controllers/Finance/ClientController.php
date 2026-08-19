<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('raison_sociale', 'ilike', "%$s%")
                ->orWhere('code', 'ilike', "%$s%")
                ->orWhere('nif', 'ilike', "%$s%")))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderBy('raison_sociale')
            ->paginate(20)
            ->withQueryString();

        return view('finance.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('finance.clients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:clients,code',
            'raison_sociale' => 'required|string|max:255',
            'forme_juridique'=> 'nullable|string|max:50',
            'nif'            => 'nullable|string|max:50',
            'rccm'           => 'nullable|string|max:50',
            'adresse'        => 'nullable|string|max:500',
            'ville'          => 'nullable|string|max:100',
            'pays'           => 'nullable|string|max:100',
            'telephone'      => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:150',
            'site_web'       => 'nullable|url|max:255',
            'contact_nom'    => 'nullable|string|max:150',
            'contact_telephone'=> 'nullable|string|max:50',
            'contact_email'  => 'nullable|email|max:150',
            'rib'            => 'nullable|string|max:50',
            'banque'         => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'statut'         => 'nullable|in:0,1',
        ]);
        $data['created_by'] = auth()->id();
        $data['statut'] ??= 1;

        $client = Client::create($data);
        return redirect()->route('finance.clients.show', $client)->with('success', "Client « {$client->raison_sociale} » créé.");
    }

    public function show(Client $client)
    {
        $client->load(['createur']);
        $factures = $client->factures()->latest()->limit(10)->get();
        return view('finance.clients.show', compact('client', 'factures'));
    }

    public function edit(Client $client)
    {
        return view('finance.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:clients,code,' . $client->id,
            'raison_sociale' => 'required|string|max:255',
            'forme_juridique'=> 'nullable|string|max:50',
            'nif'            => 'nullable|string|max:50',
            'rccm'           => 'nullable|string|max:50',
            'adresse'        => 'nullable|string|max:500',
            'ville'          => 'nullable|string|max:100',
            'pays'           => 'nullable|string|max:100',
            'telephone'      => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:150',
            'site_web'       => 'nullable|url|max:255',
            'contact_nom'    => 'nullable|string|max:150',
            'contact_telephone'=> 'nullable|string|max:50',
            'contact_email'  => 'nullable|email|max:150',
            'rib'            => 'nullable|string|max:50',
            'banque'         => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'statut'         => 'nullable|in:0,1',
        ]);
        $client->update($data);
        return redirect()->route('finance.clients.show', $client)->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client)
    {
        if ($client->factures()->exists()) {
            return back()->with('error', 'Impossible de supprimer un client ayant des factures.');
        }
        $client->delete();
        return redirect()->route('finance.clients.index')->with('success', 'Client supprimé.');
    }
}
