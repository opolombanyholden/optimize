<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetRisque;
use App\Models\User;
use Illuminate\Http\Request;

class RisqueController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['risques.responsable', 'risques.auteur']);
        $risques = $projet->risques;
        $utilisateurs = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        return view('projet.risques.index', compact('projet', 'risques', 'utilisateurs'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'titre' => 'required|string|max:255', 'description' => 'nullable|string',
            'categorie' => 'nullable|string|max:80', 'probabilite' => 'required|integer|min:1|max:5',
            'impact' => 'required|integer|min:1|max:5', 'type_risque' => 'nullable|in:menace,opportunite',
            'strategie' => 'nullable|string|max:80', 'plan_reponse' => 'nullable|string',
            'responsable_id' => 'nullable|exists:users,id', 'statut' => 'nullable|string|max:30',
        ]);

        $projet->risques()->create(array_merge(
            $request->only(['titre','description','categorie','probabilite','impact','type_risque','strategie','plan_reponse','responsable_id']),
            ['statut' => $request->input('statut', 'identifie'), 'date_identification' => now(), 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Risque enregistré.');
    }

    public function update(Request $request, Projet $projet, ProjetRisque $risque)
    {
        $risque->update($request->only([
            'titre','description','categorie','probabilite','impact','type_risque',
            'strategie','plan_reponse','plan_contingence','cout_contingence',
            'responsable_id','statut','date_revue',
        ]));
        return back()->with('success', 'Risque mis à jour.');
    }

    public function destroy(Projet $projet, ProjetRisque $risque)
    {
        $risque->delete();
        return back()->with('success', 'Risque supprimé.');
    }
}
