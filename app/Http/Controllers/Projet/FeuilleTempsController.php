<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\FeuilleTemps;
use App\Models\Intranet\Projet;
use Illuminate\Http\Request;

class FeuilleTempsController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['taches:id,titre,projet_id']);

        $feuilles = FeuilleTemps::with(['utilisateur', 'tache', 'activite', 'approbateur'])
            ->where('projet_id', $projet->id)
            ->orderByDesc('date')
            ->paginate(20);

        $stats = [
            'total_heures'    => FeuilleTemps::where('projet_id', $projet->id)->sum('heures'),
            'heures_approuvees' => FeuilleTemps::where('projet_id', $projet->id)->where('statut', 'approuve')->sum('heures'),
            'en_attente'      => FeuilleTemps::where('projet_id', $projet->id)->where('statut', 'soumis')->count(),
        ];

        return view('projet.feuilles-temps.index', compact('projet', 'feuilles', 'stats'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'tache_id'    => 'nullable|exists:intranet_taches,id',
            'activite_id' => 'nullable|exists:intranet_activites,id',
            'date'        => 'required|date',
            'heures'      => 'required|numeric|min:0.25|max:24',
            'description' => 'nullable|string',
        ]);

        FeuilleTemps::create(array_merge(
            $request->only(['tache_id', 'activite_id', 'date', 'heures', 'description']),
            ['projet_id' => $projet->id, 'user_id' => auth()->id(), 'statut' => 'soumis']
        ));

        return back()->with('success', 'Temps enregistré.');
    }

    public function approuver(Projet $projet, FeuilleTemps $feuille)
    {
        $feuille->update([
            'statut'      => 'approuve',
            'approuve_par' => auth()->id(),
            'approuve_le'  => now(),
        ]);

        return back()->with('success', 'Feuille approuvée.');
    }

    public function rejeter(Projet $projet, FeuilleTemps $feuille)
    {
        $feuille->update(['statut' => 'rejete']);
        return back()->with('success', 'Feuille rejetée.');
    }

    public function destroy(Projet $projet, FeuilleTemps $feuille)
    {
        $feuille->delete();
        return back()->with('success', 'Entrée supprimée.');
    }
}
