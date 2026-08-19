<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $fournisseurs = Fournisseur::query()
            ->when($request->search, fn($q, $s) => $q->where('raison_sociale', 'like', "%{$s}%")->orWhere('nif', 'like', "%{$s}%"))
            ->when($request->statut !== null, fn($q) => $q->where('statut', $request->statut))
            ->orderBy('raison_sociale')
            ->paginate(15);

        return view('appro.fournisseurs.index', compact('fournisseurs'));
    }

    public function create()
    {
        return view('appro.fournisseurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'raison_sociale' => 'required|string|max:255',
            'sigle' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'rccm' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|string|max:255',
            'contact_nom' => 'nullable|string|max:255',
            'contact_telephone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'rib' => 'nullable|string|max:255',
            'banque' => 'nullable|string|max:255',
            'domiciliation' => 'nullable|string|max:255',
            'categorie' => 'nullable|string|max:255',
        ]);

        Fournisseur::create($validated);

        return redirect()->route('appro.fournisseurs.index')->with('success', 'Fournisseur cree avec succes.');
    }

    public function show(string $id)
    {
        $fournisseur = Fournisseur::with(['commandes', 'produits'])->findOrFail($id);

        return view('appro.fournisseurs.show', compact('fournisseur'));
    }

    public function edit(string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);

        return view('appro.fournisseurs.edit', compact('fournisseur'));
    }

    public function update(Request $request, string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);

        $validated = $request->validate([
            'raison_sociale' => 'sometimes|required|string|max:255',
            'sigle' => 'nullable|string|max:255',
            'nif' => 'nullable|string|max:255',
            'rccm' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:255',
            'pays' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'categorie' => 'nullable|string|max:255',
            'note_evaluation' => 'nullable|numeric|min:0|max:10',
            'statut' => 'nullable|integer|in:0,1',
        ]);

        $fournisseur->update($validated);

        return redirect()->route('appro.fournisseurs.index')->with('success', 'Fournisseur mis a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->delete();

        return redirect()->route('appro.fournisseurs.index')->with('success', 'Fournisseur supprime avec succes.');
    }
}
