<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Jalon;
use App\Models\Intranet\Projet;
use Illuminate\Http\Request;

class JalonController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['jalons.phase', 'jalons.auteur', 'jalons.valideurs', 'phases']);

        $users   = \App\Models\User::orderBy('prenoms')->get();
        $groupes = \App\Models\Intranet\Groupe::orderBy('nom')->get();

        return view('projet.jalons.index', compact('projet', 'users', 'groupes'));
    }

    public function show(Projet $projet, \App\Models\Intranet\Jalon $jalon)
    {
        $jalon->load(['phase', 'auteur', 'valideurs', 'historiqueValidation.user', 'piecesJointes']);

        return view('projet.jalons.show', compact('projet', 'jalon'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'titre'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'phase_id'           => 'nullable|exists:intranet_projet_phases,id',
            'date_prevue'        => 'required|date',
            'statut'             => 'nullable|in:prevu,atteint,manque,reporte',
            'valideurs_users'    => 'nullable|array',
            'valideurs_users.*'  => 'exists:users,id',
            'valideurs_groupes'  => 'nullable|array',
            'valideurs_groupes.*'=> 'exists:intranet_groupes,id',
        ]);

        $jalon = $projet->jalons()->create(array_merge(
            $request->only(['titre', 'description', 'phase_id', 'date_prevue', 'statut']),
            ['created_by' => auth()->id(), 'statut' => $request->input('statut', 'prevu')]
        ));

        $jalon->syncValideurs(
            $request->input('valideurs_users', []),
            $request->input('valideurs_groupes', [])
        );

        if ($request->hasFile('pieces_jointes')) {
            $jalon->attacherFichiers($request->file('pieces_jointes'), 'projets/jalons');
        }

        return back()->with('success', 'Jalon créé.');
    }

    public function update(Request $request, Projet $projet, Jalon $jalon)
    {
        if (! $jalon->peutModifier()) {
            return back()->withErrors(['_protection' => 'Ce jalon est verrouillé. Utilisez "Demander modification".']);
        }

        $request->validate([
            'titre'              => 'required|string|max:255',
            'description'        => 'nullable|string',
            'phase_id'           => 'nullable|exists:intranet_projet_phases,id',
            'date_prevue'        => 'required|date',
            'date_reelle'        => 'nullable|date',
            'statut'             => 'required|in:prevu,atteint,manque,reporte',
            'valideurs_users'    => 'nullable|array',
            'valideurs_users.*'  => 'exists:users,id',
            'valideurs_groupes'  => 'nullable|array',
            'valideurs_groupes.*'=> 'exists:intranet_groupes,id',
        ]);

        // Bloquer le passage à "atteint" si la clôture n'est pas approuvée
        if ($request->input('statut') === 'atteint') {
            if ($jalon->aDesValideurs() && $jalon->statut_cloture !== 'approuve') {
                return back()->withErrors([
                    'statut' => 'Le statut "Atteint" nécessite une validation de clôture. Soumettez d\'abord le jalon pour validation.',
                ])->withInput();
            }
        }

        $jalon->update($request->only([
            'titre', 'description', 'phase_id', 'date_prevue', 'date_reelle', 'statut',
        ]));

        $jalon->syncValideurs(
            $request->input('valideurs_users', []),
            $request->input('valideurs_groupes', [])
        );

        if ($request->hasFile('pieces_jointes')) {
            $jalon->attacherFichiers($request->file('pieces_jointes'), 'projets/jalons');
        }

        return back()->with('success', 'Jalon mis à jour.');
    }

    public function destroyPiece(Projet $projet, \App\Models\Intranet\Jalon $jalon, int $piece)
    {
        $jalon->detacherFichier($piece);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    public function destroy(Projet $projet, Jalon $jalon)
    {
        if (! $jalon->peutSupprimer()) {
            return back()->withErrors(['_protection' => 'Ce jalon est verrouillé. Utilisez "Demander suppression".']);
        }

        $jalon->delete();
        return back()->with('success', 'Jalon supprimé.');
    }

    public function marquerAtteint(Projet $projet, Jalon $jalon)
    {
        // Bloquer si des valideurs existent et la clôture n'est pas approuvée
        if ($jalon->aDesValideurs() && $jalon->statut_cloture !== 'approuve') {
            return back()->withErrors([
                'statut' => 'Ce jalon nécessite une validation de clôture avant d\'être marqué comme atteint.',
            ]);
        }

        $jalon->update([
            'statut'      => 'atteint',
            'date_reelle' => now()->toDateString(),
        ]);

        return back()->with('success', 'Jalon marqué comme atteint.');
    }
}
