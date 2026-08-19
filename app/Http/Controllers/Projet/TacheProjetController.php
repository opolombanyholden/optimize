<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Priorite;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\Statut;
use App\Models\Intranet\Tache;
use App\Models\User;
use Illuminate\Http\Request;

class TacheProjetController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load([
            'taches.statut', 'taches.priorite', 'taches.responsable', 'taches.phase',
            'phases',
        ]);

        $taches    = $projet->taches;
        $statuts   = Statut::orderBy('id')->get();
        $priorites = Priorite::orderBy('id')->get();
        $users     = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);

        return view('projet.taches.index', compact('projet', 'taches', 'statuts', 'priorites', 'users'));
    }

    public function store(Request $request, Projet $projet)
    {
        $rules = [
            'titre'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'phase_id'        => 'nullable|exists:intranet_projet_phases,id',
            'statut_id'       => 'nullable|exists:intranet_statuts,id',
            'priorite_id'     => 'nullable|exists:intranet_priorites,id',
            'responsable_id'  => 'nullable|exists:users,id',
            'date_debut'      => 'nullable|date',
            'date_fin'        => 'nullable|date|after_or_equal:date_debut',
            'heures_estimees' => 'nullable|numeric|min:0',
            'cout_execution'  => 'nullable|numeric|min:0',
            'ponderation'     => 'nullable|numeric|min:0|max:100',
            'avancement'      => 'nullable|integer|min:0|max:100',
        ];

        // Bornes dates projet
        if ($projet->date_debut) $rules['date_debut'] .= '|after_or_equal:' . $projet->date_debut->format('Y-m-d');
        if ($projet->date_fin)   $rules['date_fin']   .= '|before_or_equal:' . $projet->date_fin->format('Y-m-d');

        $request->validate($rules);

        // La phase doit appartenir au projet (sécurité)
        if ($request->phase_id) {
            $phaseAppartientAuProjet = ProjetPhase::where('id', $request->phase_id)
                ->where('projet_id', $projet->id)->exists();
            if (! $phaseAppartientAuProjet) {
                return back()->withErrors(['phase_id' => 'La phase ne fait pas partie de ce projet.'])->withInput();
            }
        }

        $maxOrdre = $projet->taches()->max('ordre') ?? 0;

        $projet->taches()->create(array_merge(
            $request->only([
                'titre', 'description', 'phase_id', 'statut_id', 'priorite_id',
                'responsable_id', 'date_debut', 'date_fin', 'heures_estimees',
                'cout_execution', 'ponderation', 'avancement',
            ]),
            ['ordre' => $maxOrdre + 1, 'created_by' => auth()->id()]
        ));

        // Recalculer cascade
        $projet->recalculerAvancement();

        return back()->with('success', 'Tâche créée.');
    }

    public function update(Request $request, Projet $projet, Tache $tache)
    {
        if ($tache->projet_id !== $projet->id) abort(404);

        if (! $tache->peutModifier()) {
            return back()->withErrors(['_protection' => 'Tâche verrouillée. Utilisez "Demander modification".']);
        }

        $request->validate([
            'titre'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'phase_id'        => 'nullable|exists:intranet_projet_phases,id',
            'statut_id'       => 'nullable|exists:intranet_statuts,id',
            'priorite_id'     => 'nullable|exists:intranet_priorites,id',
            'responsable_id'  => 'nullable|exists:users,id',
            'date_debut'      => 'nullable|date',
            'date_fin'        => 'nullable|date|after_or_equal:date_debut',
            'heures_estimees' => 'nullable|numeric|min:0',
            'cout_execution'  => 'nullable|numeric|min:0',
            'ponderation'     => 'nullable|numeric|min:0|max:100',
            'avancement'      => 'nullable|integer|min:0|max:100',
        ]);

        $tache->update($request->only([
            'titre', 'description', 'phase_id', 'statut_id', 'priorite_id',
            'responsable_id', 'date_debut', 'date_fin', 'heures_estimees',
            'cout_execution', 'ponderation', 'avancement',
        ]));

        // Recalculer cascade
        $tache->phase?->recalculerAvancement();
        $projet->recalculerAvancement();

        return back()->with('success', 'Tâche mise à jour.');
    }

    public function destroy(Projet $projet, Tache $tache)
    {
        if ($tache->projet_id !== $projet->id) abort(404);

        if (! $tache->peutSupprimer()) {
            return back()->withErrors(['_protection' => 'Tâche verrouillée. Utilisez "Demander suppression".']);
        }

        $phase = $tache->phase;
        $tache->delete();

        $phase?->recalculerAvancement();
        $projet->recalculerAvancement();

        return back()->with('success', 'Tâche supprimée.');
    }
}
