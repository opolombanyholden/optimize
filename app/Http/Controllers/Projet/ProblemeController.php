<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetProbleme;
use App\Models\User;
use Illuminate\Http\Request;

class ProblemeController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['problemes.responsable', 'problemes.priorite', 'problemes.risque']);
        $problemes = $projet->problemes;
        $utilisateurs = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        return view('projet.problemes.index', compact('projet', 'problemes', 'utilisateurs'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'titre' => 'required|string|max:255', 'description' => 'nullable|string',
            'priorite_id' => 'nullable|exists:intranet_priorites,id',
            'responsable_id' => 'nullable|exists:users,id',
            'impact' => 'nullable|string', 'statut' => 'nullable|in:ouvert,en_cours,resolu,ferme',
        ]);

        $projet->problemes()->create(array_merge(
            $request->only(['titre','description','priorite_id','responsable_id','impact']),
            ['statut' => $request->input('statut', 'ouvert'), 'date_identification' => now(), 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Problème enregistré.');
    }

    public function update(Request $request, Projet $projet, ProjetProbleme $probleme)
    {
        $probleme->update($request->only(['titre','description','priorite_id','responsable_id','impact','resolution','statut','date_resolution']));
        return back()->with('success', 'Problème mis à jour.');
    }

    public function destroy(Projet $projet, ProjetProbleme $probleme)
    {
        $probleme->delete();
        return back()->with('success', 'Problème supprimé.');
    }
}
