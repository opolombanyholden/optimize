<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intranet\RoleProjet;
use Illuminate\Http\Request;

class RoleProjetController extends Controller
{
    public function index()
    {
        $roles = RoleProjet::orderBy('ordre')->get();
        return view('admin.roles-projet.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle'     => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:intranet_roles_projet,code',
            'couleur'     => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $maxOrdre = RoleProjet::max('ordre') ?? 0;

        RoleProjet::create(array_merge(
            $request->only(['libelle', 'code', 'couleur', 'description']),
            ['ordre' => $maxOrdre + 1]
        ));

        return back()->with('success', 'Rôle projet créé.');
    }

    public function update(Request $request, RoleProjet $role)
    {
        $request->validate([
            'libelle'     => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:intranet_roles_projet,code,' . $role->id,
            'couleur'     => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'est_actif'   => 'nullable|boolean',
        ]);

        $role->update($request->only(['libelle', 'code', 'couleur', 'description'])
            + ['est_actif' => $request->boolean('est_actif', true)]);

        return back()->with('success', 'Rôle projet mis à jour.');
    }

    public function destroy(RoleProjet $role)
    {
        if ($role->ressources()->exists()) {
            return back()->withErrors(['_error' => 'Ce rôle est utilisé par des ressources. Désactivez-le plutôt.']);
        }

        $role->delete();
        return back()->with('success', 'Rôle projet supprimé.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->input('order') as $index => $id) {
            RoleProjet::where('id', $id)->update(['ordre' => $index]);
        }

        return response()->json(['ok' => true]);
    }
}
