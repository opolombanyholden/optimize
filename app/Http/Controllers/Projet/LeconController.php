<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetLecon;
use Illuminate\Http\Request;

class LeconController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['lecons.auteur']);
        $lecons = $projet->lecons;
        return view('projet.lecons.index', compact('projet', 'lecons'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'titre' => 'required|string|max:255', 'description' => 'nullable|string',
            'type' => 'nullable|in:positive,negative,suggestion',
            'categorie' => 'nullable|string|max:80',
            'impact' => 'nullable|string', 'recommandation' => 'nullable|string',
        ]);

        $projet->lecons()->create(array_merge(
            $request->only(['titre','description','type','categorie','impact','recommandation']),
            ['statut' => 'identifie', 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Leçon enregistrée.');
    }

    public function update(Request $request, Projet $projet, ProjetLecon $lecon)
    {
        $lecon->update($request->only(['titre','description','type','categorie','impact','recommandation','statut']));
        return back()->with('success', 'Leçon mise à jour.');
    }

    public function destroy(Projet $projet, ProjetLecon $lecon)
    {
        $lecon->delete();
        return back()->with('success', 'Leçon supprimée.');
    }
}
