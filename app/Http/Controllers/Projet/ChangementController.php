<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetChangement;
use Illuminate\Http\Request;

class ChangementController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['changements.demandeur', 'changements.approbateur']);
        $changements = $projet->changements;
        $utilisateurs = \App\Models\User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        return view('projet.changements.index', compact('projet', 'changements', 'utilisateurs'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'titre' => 'required|string|max:255', 'description' => 'nullable|string',
            'type' => 'nullable|in:perimetre,delai,cout,qualite,autre',
            'justification' => 'nullable|string', 'impact_cout' => 'nullable|numeric',
            'impact_delai_jours' => 'nullable|integer',
        ]);

        $projet->changements()->create(array_merge(
            $request->only(['titre','description','type','justification','impact_cout','impact_delai_jours','impact_qualite','impact_risques']),
            ['statut' => 'soumis', 'demandeur_id' => auth()->id(), 'date_soumission' => now(), 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Demande de changement soumise.');
    }

    public function decider(Request $request, Projet $projet, ProjetChangement $changement)
    {
        $request->validate([
            'statut' => 'required|in:approuve,rejete,en_evaluation',
            'decision_commentaire' => 'nullable|string',
        ]);

        $changement->update([
            'statut'               => $request->statut,
            'approuve_par'         => auth()->id(),
            'date_decision'        => now(),
            'decision_commentaire' => $request->decision_commentaire,
        ]);

        return back()->with('success', 'Décision enregistrée.');
    }

    public function destroy(Projet $projet, ProjetChangement $changement)
    {
        $changement->delete();
        return back()->with('success', 'Demande supprimée.');
    }
}
