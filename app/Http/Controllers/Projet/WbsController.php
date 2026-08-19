<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\Statut;
use Illuminate\Http\Request;

class WbsController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load([
            'phases.taches.statut', 'phases.taches.responsable',
            'phases.responsable', 'phases.statut', 'phases.valideurs',
        ]);

        $ponderationAllouee = $projet->phases->sum('ponderation');
        $statuts  = Statut::orderBy('id')->get();
        $users    = \App\Models\User::orderBy('prenoms')->get();
        $groupes  = \App\Models\Intranet\Groupe::orderBy('nom')->get();

        return view('projet.wbs.index', compact('projet', 'ponderationAllouee', 'statuts', 'users', 'groupes'));
    }

    public function show(Projet $projet, ProjetPhase $phase)
    {
        $phase->load([
            'taches.statut', 'taches.priorite', 'taches.responsable',
            'responsable', 'statut', 'auteur', 'valideurs',
            'couts.auteur', 'jalons', 'livrables',
            'historiqueValidation.user', 'piecesJointes',
        ]);

        return view('projet.wbs.show', compact('projet', 'phase'));
    }

    public function store(Request $request, Projet $projet)
    {
        $rules = [
            'nom'              => 'required|string|max:255',
            'description'      => 'nullable|string',
            'code_wbs'         => 'nullable|string|max:20',
            'statut_id'        => 'nullable|exists:intranet_statuts,id',
            'responsable_id'   => 'nullable|exists:users,id',
            'couleur'          => 'nullable|string|max:20',
            'ponderation'      => 'nullable|numeric|min:0|max:100',
            'date_debut'       => 'nullable|date',
            'date_fin'         => 'nullable|date|after_or_equal:date_debut',
            'date_debut_reelle'=> 'nullable|date',
            'date_fin_reelle'  => 'nullable|date',
            'valideurs_users'    => 'nullable|array',
            'valideurs_users.*'  => 'exists:users,id',
            'valideurs_groupes'  => 'nullable|array',
            'valideurs_groupes.*'=> 'exists:intranet_groupes,id',
        ];

        // Validation dynamique : dates dans les bornes du projet
        if ($projet->date_debut) {
            $rules['date_debut'] .= '|after_or_equal:' . $projet->date_debut->format('Y-m-d');
        }
        if ($projet->date_fin) {
            $rules['date_fin'] .= '|before_or_equal:' . $projet->date_fin->format('Y-m-d');
        }

        $request->validate($rules, [
            'date_debut.after_or_equal' => 'La date de début ne peut pas être antérieure à celle du projet (' . $projet->date_debut?->format('d/m/Y') . ').',
            'date_fin.before_or_equal'  => 'La date de fin ne peut pas dépasser celle du projet (' . $projet->date_fin?->format('d/m/Y') . ').',
            'date_fin.after_or_equal'   => 'La date de fin doit être ≥ à la date de début.',
        ]);

        // Vérifier que la pondération totale ne dépasse pas 100%
        $ponderationActuelle = $projet->phases->sum('ponderation');
        $nouvellePonderation = (float) $request->input('ponderation', 0);
        if ($ponderationActuelle + $nouvellePonderation > 100) {
            return back()->withErrors([
                'ponderation' => "La pondération totale dépasserait 100%. Disponible : " . round(100 - $ponderationActuelle, 2) . "%.",
            ])->withInput();
        }

        $maxOrdre = $projet->phases()->max('ordre') ?? 0;

        $phase = $projet->phases()->create(array_merge(
            $request->only([
                'nom', 'description', 'code_wbs', 'statut_id',
                'date_debut', 'date_fin', 'date_debut_reelle', 'date_fin_reelle',
                'responsable_id', 'couleur', 'ponderation',
            ]),
            ['ordre' => $maxOrdre + 1, 'created_by' => auth()->id()]
        ));

        // Synchroniser les valideurs
        $phase->syncValideurs(
            $request->input('valideurs_users', []),
            $request->input('valideurs_groupes', [])
        );

        // Pièces jointes
        if ($request->hasFile('pieces_jointes')) {
            $phase->attacherFichiers($request->file('pieces_jointes'), 'projets/phases');
        }

        $projet->recalculerAvancement();

        return back()->with('success', 'Phase WBS créée.');
    }

    public function update(Request $request, Projet $projet, ProjetPhase $phase)
    {
        if (! $phase->peutModifier()) {
            return back()->withErrors(['_protection' => 'Cette phase est verrouillée. Utilisez "Demander modification" pour soumettre vos changements.']);
        }

        $rules = [
            'nom'              => 'required|string|max:255',
            'description'      => 'nullable|string',
            'code_wbs'         => 'nullable|string|max:20',
            'statut_id'        => 'nullable|exists:intranet_statuts,id',
            'responsable_id'   => 'nullable|exists:users,id',
            'couleur'          => 'nullable|string|max:20',
            'avancement'       => 'nullable|integer|min:0|max:100',
            'ponderation'      => 'nullable|numeric|min:0|max:100',
            'date_debut'       => 'nullable|date',
            'date_fin'         => 'nullable|date|after_or_equal:date_debut',
            'date_debut_reelle'=> 'nullable|date',
            'date_fin_reelle'  => 'nullable|date',
            'valideurs_users'    => 'nullable|array',
            'valideurs_users.*'  => 'exists:users,id',
            'valideurs_groupes'  => 'nullable|array',
            'valideurs_groupes.*'=> 'exists:intranet_groupes,id',
        ];

        if ($projet->date_debut) {
            $rules['date_debut'] .= '|after_or_equal:' . $projet->date_debut->format('Y-m-d');
        }
        if ($projet->date_fin) {
            $rules['date_fin'] .= '|before_or_equal:' . $projet->date_fin->format('Y-m-d');
        }

        $request->validate($rules, [
            'date_debut.after_or_equal' => 'La date de début ne peut pas être antérieure à celle du projet (' . $projet->date_debut?->format('d/m/Y') . ').',
            'date_fin.before_or_equal'  => 'La date de fin ne peut pas dépasser celle du projet (' . $projet->date_fin?->format('d/m/Y') . ').',
        ]);

        // Bloquer le passage à "Terminé" si la clôture n'est pas approuvée
        $statutTermine = Statut::where('libelle', 'Terminé')->first();
        if ($statutTermine && (int) $request->input('statut_id') === $statutTermine->id) {
            if ($phase->aDesValideurs() && $phase->statut_cloture !== 'approuve') {
                return back()->withErrors([
                    'statut_id' => 'Le statut "Terminé" nécessite une validation de clôture. Soumettez d\'abord la phase pour validation.',
                ])->withInput();
            }
        }

        // Vérifier pondération (exclure la phase courante du calcul)
        $ponderationAutres = $projet->phases->where('id', '!=', $phase->id)->sum('ponderation');
        $nouvellePonderation = (float) $request->input('ponderation', 0);
        if ($ponderationAutres + $nouvellePonderation > 100) {
            return back()->withErrors([
                'ponderation' => "La pondération totale dépasserait 100%. Disponible : " . round(100 - $ponderationAutres, 2) . "%.",
            ])->withInput();
        }

        $phase->update($request->only([
            'nom', 'description', 'code_wbs', 'statut_id',
            'date_debut', 'date_fin', 'date_debut_reelle', 'date_fin_reelle',
            'responsable_id', 'couleur', 'avancement', 'ponderation',
        ]));

        // Synchroniser les valideurs
        $phase->syncValideurs(
            $request->input('valideurs_users', []),
            $request->input('valideurs_groupes', [])
        );

        // Pièces jointes
        if ($request->hasFile('pieces_jointes')) {
            $phase->attacherFichiers($request->file('pieces_jointes'), 'projets/phases');
        }

        $projet->recalculerAvancement();

        return back()->with('success', 'Phase mise à jour.');
    }

    public function destroy(Projet $projet, ProjetPhase $phase)
    {
        if (! $phase->peutSupprimer()) {
            return back()->withErrors(['_protection' => 'Cette phase est verrouillée. Utilisez "Demander suppression" pour soumettre votre demande.']);
        }

        $phase->delete();
        return back()->with('success', 'Phase supprimée.');
    }

    public function destroyPiece(Projet $projet, ProjetPhase $phase, int $piece)
    {
        $phase->detacherFichier($piece);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    public function reorder(Request $request, Projet $projet)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->input('order') as $index => $phaseId) {
            ProjetPhase::where('id', $phaseId)->where('projet_id', $projet->id)
                ->update(['ordre' => $index]);
        }

        return response()->json(['ok' => true]);
    }
}
