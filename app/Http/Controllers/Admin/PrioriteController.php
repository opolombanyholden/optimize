<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Priorite;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Référentiel intranet_priorites — niveaux de priorité pour tâches, événements, etc.
 */
class PrioriteController extends Controller
{
    public function index()
    {
        $priorites = Priorite::orderBy('libelle')->get();
        return view('admin.priorites.index', compact('priorites'));
    }

    public function store(Request $request)
    {
        Priorite::create($this->validateData($request));
        return back()->with('success', 'Priorité créée.');
    }

    public function update(Request $request, Priorite $priorite)
    {
        $priorite->update($this->validateData($request, $priorite));
        return back()->with('success', 'Priorité mise à jour.');
    }

    public function destroy(Priorite $priorite)
    {
        if (in_array($priorite->libelle, ['Basse', 'Normale', 'Haute', 'Critique'], true)) {
            return back()->with('error', "Priorité système '{$priorite->libelle}' non supprimable.");
        }
        $priorite->delete();
        return back()->with('success', 'Priorité supprimée.');
    }

    private function validateData(Request $request, ?Priorite $priorite = null): array
    {
        return $request->validate([
            'libelle' => ['required', 'string', 'max:50', Rule::unique('intranet_priorites', 'libelle')->ignore($priorite?->id)],
            'couleur' => 'nullable|string|max:20',
        ]);
    }
}
