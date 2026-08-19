<?php

namespace App\Http\Controllers\Intranet\Annuaire;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Groupe;
use App\Models\User;
use Illuminate\Http\Request;

class EquipeController extends Controller
{
    public function index()
    {
        $equipes = Groupe::with('auteur')
            ->withCount('membres')
            ->orderBy('nom')
            ->get();

        $users = User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']);

        return view('intranet.annuaire.equipes.index', compact('equipes', 'users'));
    }

    public function show(Groupe $equipe)
    {
        $equipe->load(['membres' => fn($q) => $q->orderBy('name'), 'auteur']);

        return view('intranet.annuaire.equipes.show', compact('equipe'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'couleur'     => 'nullable|string|max:20',
            'icone'       => 'nullable|string|max:50',
            'membres'     => 'nullable|array',
            'membres.*'   => 'exists:users,id',
        ]);

        $equipe = Groupe::create(array_merge(
            collect($validated)->except('membres')->toArray(),
            ['created_by' => auth()->id()]
        ));

        if (!empty($validated['membres'])) {
            foreach ($validated['membres'] as $userId) {
                $equipe->membres()->attach($userId, ['role' => 'membre']);
            }
        }

        return redirect()->route('intranet.annuaire.equipes.index')->with('success', 'Équipe créée.');
    }

    public function update(Request $request, Groupe $equipe)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'couleur'     => 'nullable|string|max:20',
            'icone'       => 'nullable|string|max:50',
        ]);

        $equipe->update($validated);

        return back()->with('success', 'Équipe mise à jour.');
    }

    public function destroy(Groupe $equipe)
    {
        $equipe->delete();
        return back()->with('success', 'Équipe supprimée.');
    }

    public function attacherMembre(Request $request, Groupe $equipe)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'nullable|in:membre,animateur',
        ]);

        $equipe->membres()->syncWithoutDetaching([
            $validated['user_id'] => ['role' => $validated['role'] ?? 'membre'],
        ]);

        return back()->with('success', 'Membre ajouté.');
    }

    public function detacherMembre(Groupe $equipe, User $user)
    {
        $equipe->membres()->detach($user->id);
        return back()->with('success', 'Membre retiré.');
    }
}
