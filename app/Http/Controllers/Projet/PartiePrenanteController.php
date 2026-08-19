<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPartiePrenante;
use App\Models\User;
use Illuminate\Http\Request;

class PartiePrenanteController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['partiesPrenantes.utilisateur', 'partiesPrenantes.contact']);
        $partiesPrenantes = $projet->partiesPrenantes;
        $utilisateurs = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        return view('projet.parties-prenantes.index', compact('projet', 'partiesPrenantes', 'utilisateurs'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id', 'nom_externe' => 'nullable|string|max:255',
            'organisation_externe' => 'nullable|string|max:255', 'role_projet' => 'nullable|string|max:255',
            'categorie' => 'nullable|in:interne,externe,cle,secondaire',
            'interet' => 'nullable|integer|min:1|max:5', 'influence' => 'nullable|integer|min:1|max:5',
            'strategie_engagement' => 'nullable|string', 'attentes' => 'nullable|string',
        ]);

        $projet->partiesPrenantes()->create(array_merge(
            $request->only(['user_id','nom_externe','organisation_externe','role_projet','categorie','interet','influence','engagement_actuel','engagement_desire','strategie_engagement','attentes','preoccupations']),
            ['created_by' => auth()->id()]
        ));

        return back()->with('success', 'Partie prenante ajoutée.');
    }

    public function update(Request $request, Projet $projet, ProjetPartiePrenante $partie)
    {
        $partie->update($request->only([
            'role_projet','categorie','interet','influence','engagement_actuel','engagement_desire','strategie_engagement','attentes','preoccupations',
        ]));
        return back()->with('success', 'Mise à jour effectuée.');
    }

    public function destroy(Projet $projet, ProjetPartiePrenante $partie)
    {
        $partie->delete();
        return back()->with('success', 'Partie prenante retirée.');
    }
}
