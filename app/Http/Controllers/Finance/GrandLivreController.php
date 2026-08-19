<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\GrandLivre;
use Illuminate\Http\Request;

class GrandLivreController extends Controller
{
    public function index(Request $request)
    {
        $ecritures = GrandLivre::query()
            ->with(['entite', 'compte', 'user'])
            ->when($request->exercice_id, fn($q, $id) => $q->where('id_exercicebudgetaire', $id))
            ->when($request->compte_id, fn($q, $id) => $q->where('compte_id', $id))
            ->when($request->ref_piece, fn($q, $ref) => $q->where('ref_piece', $ref))
            ->when($request->journal, fn($q, $j) => $q->where('journal', $j))
            ->when($request->date_debut, fn($q, $d) => $q->where('date_ecriture', '>=', $d))
            ->when($request->date_fin, fn($q, $d) => $q->where('date_ecriture', '<=', $d))
            ->when($request->search, fn($q, $s) => $q->where('libelle', 'like', "%{$s}%"))
            ->orderByDesc('date_ecriture')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Pour les filtres
        $exercices = Exercice::orderByDesc('id')->get();
        $comptes   = Compte::orderBy('id')->get();

        return view('finance.grand-livre.index', compact('ecritures', 'exercices', 'comptes'));
    }

    public function create()
    {
        $exercices = Exercice::where('statut', 1)->get();

        return view('finance.grand-livre.create', compact('exercices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_ecriture' => 'required|date',
            'id_entite' => 'required|exists:entites,id',
            'compte_id' => 'required|exists:comptes,id',
            'montant_tc' => 'required|numeric',
            'sens' => 'required|string|in:debit,credit',
            'libelle' => 'required|string|max:255',
            'mode_reglement' => 'nullable|string|max:255',
            'id_exercicebudgetaire' => 'nullable|exists:exercices,id',
            'description' => 'nullable|string',
            'beneficiaire' => 'nullable|string|max:255',
            'type_beneficiaire' => 'nullable|string|max:255',
            'num_piece' => 'nullable|string|max:255',
            'nature_piece' => 'nullable|string|max:255',
            'journal' => 'nullable|string|max:255',
            'devise' => 'nullable|string|max:100',
        ]);

        $validated['id_user'] = $request->user()->id;
        GrandLivre::create($validated);

        return redirect()->route('finance.grand-livre.index')->with('success', 'Ecriture creee avec succes.');
    }

    public function show(string $id)
    {
        $ecriture = GrandLivre::with(['details', 'exerciceBudgetaire', 'entite', 'compte', 'user'])->findOrFail($id);

        return view('finance.grand-livre.show', compact('ecriture'));
    }

    public function edit(string $id)
    {
        $ecriture = GrandLivre::findOrFail($id);
        $exercices = Exercice::where('statut', 1)->get();

        return view('finance.grand-livre.edit', compact('ecriture', 'exercices'));
    }

    public function update(Request $request, string $id)
    {
        $ecriture = GrandLivre::findOrFail($id);

        $validated = $request->validate([
            'date_ecriture' => 'sometimes|date',
            'montant_tc' => 'sometimes|numeric',
            'sens' => 'sometimes|string|in:debit,credit',
            'libelle' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'isvalide' => 'nullable|integer|in:0,1',
        ]);

        $ecriture->update($validated);

        return redirect()->route('finance.grand-livre.index')->with('success', 'Ecriture mise a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $ecriture = GrandLivre::findOrFail($id);
        $ecriture->delete();

        return redirect()->route('finance.grand-livre.index')->with('success', 'Ecriture supprimee avec succes.');
    }
}
