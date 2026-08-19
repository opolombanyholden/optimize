<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetRessource;
use App\Models\Intranet\RoleProjet;
use App\Models\User;
use Illuminate\Http\Request;

class RessourceController extends Controller
{
    public function index(Projet $projet)
    {
        $projet->load(['ressources.utilisateur', 'ressources.roleProjet']);
        $utilisateurs = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);
        $rolesProjet  = RoleProjet::actif()->orderBy('ordre')->get();

        return view('projet.ressources.index', compact('projet', 'utilisateurs', 'rolesProjet'));
    }

    public function store(Request $request, Projet $projet)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'role_projet_id'  => 'nullable|exists:intranet_roles_projet,id',
            'heures_allouees' => 'nullable|numeric|min:0',
            'taux_journalier' => 'nullable|numeric|min:0',
            'date_debut'      => 'nullable|date',
            'date_fin'        => 'nullable|date|after_or_equal:date_debut',
        ]);

        $projet->ressources()->create(array_merge(
            $request->only(['user_id', 'role_projet_id', 'heures_allouees', 'taux_journalier', 'date_debut', 'date_fin']),
            ['est_actif' => true]
        ));

        return back()->with('success', 'Ressource ajoutée.');
    }

    public function update(Request $request, Projet $projet, ProjetRessource $ressource)
    {
        $request->validate([
            'role_projet_id'  => 'nullable|exists:intranet_roles_projet,id',
            'heures_allouees' => 'nullable|numeric|min:0',
            'heures_reelles'  => 'nullable|numeric|min:0',
            'taux_journalier' => 'nullable|numeric|min:0',
            'date_debut'      => 'nullable|date',
            'date_fin'        => 'nullable|date',
            'est_actif'       => 'nullable|boolean',
        ]);

        $ressource->update($request->only([
            'role_projet_id', 'heures_allouees', 'heures_reelles',
            'taux_journalier', 'date_debut', 'date_fin',
        ]) + ['est_actif' => $request->boolean('est_actif')]);

        return back()->with('success', 'Ressource mise à jour.');
    }

    public function destroy(Projet $projet, ProjetRessource $ressource)
    {
        $ressource->delete();
        return back()->with('success', 'Ressource retirée.');
    }
}
