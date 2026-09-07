<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Statut;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Référentiel intranet_statuts — statuts génériques utilisés par tâches, projets, etc.
 */
class StatutController extends Controller
{
    public function index()
    {
        $statuts = Statut::orderBy('libelle')->get();
        return view('admin.statuts.index', compact('statuts'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Statut::create($data);
        return back()->with('success', 'Statut créé.');
    }

    public function update(Request $request, Statut $statut)
    {
        $statut->update($this->validateData($request, $statut));
        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Statut $statut)
    {
        // Protège contre suppression d'un statut système utilisé
        if (in_array($statut->libelle, ['Non démarré', 'En cours', 'Terminé', 'Annulé'], true)) {
            return back()->with('error', "Statut système '{$statut->libelle}' non supprimable.");
        }
        $statut->delete();
        return back()->with('success', 'Statut supprimé.');
    }

    private function validateData(Request $request, ?Statut $statut = null): array
    {
        return $request->validate([
            'libelle'   => ['required', 'string', 'max:50', Rule::unique('intranet_statuts', 'libelle')->ignore($statut?->id)],
            'couleur'   => 'nullable|string|max:20',
        ]);
    }
}
