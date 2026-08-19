<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\Dysfonctionnement;
use App\Models\Intervention;
use App\Models\MgThematique;
use Illuminate\Http\Request;

class MgThematiqueController extends Controller
{
    public function index()
    {
        $racines = MgThematique::whereNull('parent_id')
            ->with(['enfants.enfants.enfants'])
            ->orderBy('ordre')->orderBy('libelle')->get();

        return view('referentiel.mg-thematiques.index', [
            'racines' => $racines,
            'toutes' => MgThematique::orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        MgThematique::create($this->validateData($request) + ['actif' => true]);
        return back()->with('success', 'Thématique créée.');
    }

    public function update(Request $request, MgThematique $thematique)
    {
        $data = $this->validateData($request, $thematique->id);

        if (!empty($data['parent_id']) && (int) $data['parent_id'] === $thematique->id) {
            return back()->with('error', 'Une thématique ne peut pas être son propre parent.');
        }
        if (!empty($data['parent_id']) && in_array((int) $data['parent_id'], $thematique->descendantsIds(), true)) {
            return back()->with('error', 'Impossible d\'affecter un descendant comme parent (cycle).');
        }
        $data['actif'] = $request->boolean('actif');

        $thematique->update($data);
        return back()->with('success', 'Thématique mise à jour.');
    }

    public function destroy(MgThematique $thematique)
    {
        // Bloque suppression si sous-thématiques
        if ($thematique->enfants()->exists()) {
            return back()->with('error', 'Cette thématique contient des sous-thématiques ; supprimez-les ou déplacez-les d\'abord.');
        }
        // Bloque suppression si utilisée (récursif) par dysfonc ou interv
        $descIds = $thematique->descendantsIds();
        $usedDysfonc = Dysfonctionnement::whereIn('thematique_id', $descIds)->count();
        $usedInterv  = Intervention::whereIn('thematique_id', $descIds)->count();
        if ($usedDysfonc + $usedInterv > 0) {
            return back()->with('error', "Suppression impossible : thématique utilisée par {$usedDysfonc} dysfonctionnement(s) et {$usedInterv} intervention(s).");
        }

        $thematique->delete();
        return back()->with('success', 'Thématique supprimée.');
    }

    public function toggle(MgThematique $thematique)
    {
        $thematique->update(['actif' => !$thematique->actif]);
        return back()->with('success', $thematique->actif ? 'Thématique activée.' : 'Thématique désactivée.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'libelle' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:mg_thematiques,id',
            'description' => 'nullable|string|max:1000',
            'couleur' => 'nullable|string|max:20',
            'ordre' => 'nullable|integer',
        ]);
    }
}
