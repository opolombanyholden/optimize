<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetLivrable;
use Illuminate\Http\Request;

class LivrableController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['livrables.phase', 'livrables.tache', 'livrables.responsable', 'phases', 'taches']);
        return view('projet.livrables.index', compact('projet'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'titre'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'criteres_acceptation' => 'nullable|string',
            'phase_id'             => 'nullable|exists:intranet_projet_phases,id',
            'tache_id'             => 'nullable|exists:intranet_taches,id',
            'responsable_id'       => 'nullable|exists:users,id',
            'date_prevue'          => 'nullable|date',
            'statut'               => 'nullable|in:planifie,en_cours,livre,accepte,refuse',
        ]);

        $projet->livrables()->create(array_merge(
            $request->only(['titre', 'description', 'criteres_acceptation', 'phase_id', 'tache_id', 'responsable_id', 'date_prevue']),
            ['statut' => $request->input('statut', 'planifie'), 'created_by' => auth()->id()]
        ));

        return back()->with('success', 'Livrable créé.');
    }

    public function update(Request $request, Projet $projet, ProjetLivrable $livrable)
    {
        $request->validate([
            'titre'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'criteres_acceptation' => 'nullable|string',
            'phase_id'             => 'nullable|exists:intranet_projet_phases,id',
            'tache_id'             => 'nullable|exists:intranet_taches,id',
            'responsable_id'       => 'nullable|exists:users,id',
            'date_prevue'          => 'nullable|date',
            'date_livraison'       => 'nullable|date',
            'statut'               => 'required|in:planifie,en_cours,livre,accepte,refuse',
        ]);

        $livrable->update($request->only([
            'titre', 'description', 'criteres_acceptation',
            'phase_id', 'tache_id', 'responsable_id',
            'date_prevue', 'date_livraison', 'statut',
        ]));

        return back()->with('success', 'Livrable mis à jour.');
    }

    public function destroy(Projet $projet, ProjetLivrable $livrable)
    {
        $livrable->delete();
        return back()->with('success', 'Livrable supprimé.');
    }
}
