<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\CritereEvaluation;
use App\Models\ThemeEvaluation;
use Illuminate\Http\Request;

class EvaluationReferentielController extends Controller
{
    public function index()
    {
        $themes = ThemeEvaluation::with(['criteres' => fn($q) => $q->orderBy('ordre')])->orderBy('ordre')->get();
        $criteresSansTheme = CritereEvaluation::whereNull('theme_id')->orderBy('ordre')->get();
        return view('referentiel.evaluations.index', compact('themes', 'criteresSansTheme'));
    }

    // ═════════ Thèmes ═════════

    public function storeTheme(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'ordre' => 'nullable|integer',
        ]);
        ThemeEvaluation::create($data + ['actif' => true, 'ordre' => $data['ordre'] ?? 0]);
        return back()->with('success', 'Thème créé.');
    }

    public function updateTheme(Request $request, ThemeEvaluation $theme)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'ordre' => 'nullable|integer',
            'actif' => 'nullable|boolean',
        ]);
        $theme->update($data);
        return back()->with('success', 'Thème mis à jour.');
    }

    public function destroyTheme(ThemeEvaluation $theme)
    {
        $theme->delete();
        return back()->with('success', 'Thème supprimé.');
    }

    // ═════════ Critères ═════════

    public function storeCritere(Request $request)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'theme_id' => 'nullable|exists:themes_evaluation,id',
            'echelle_min' => 'required|integer|min:0|max:100',
            'echelle_max' => 'required|integer|min:1|max:100|gte:echelle_min',
            'poids' => 'required|numeric|min:0.01|max:100',
            'ordre' => 'nullable|integer',
        ]);
        CritereEvaluation::create($data + ['actif' => true, 'ordre' => $data['ordre'] ?? 0]);
        return back()->with('success', 'Critère créé.');
    }

    public function updateCritere(Request $request, CritereEvaluation $critere)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'theme_id' => 'nullable|exists:themes_evaluation,id',
            'echelle_min' => 'required|integer|min:0|max:100',
            'echelle_max' => 'required|integer|min:1|max:100|gte:echelle_min',
            'poids' => 'required|numeric|min:0.01|max:100',
            'ordre' => 'nullable|integer',
            'actif' => 'nullable|boolean',
        ]);
        $critere->update($data);
        return back()->with('success', 'Critère mis à jour.');
    }

    public function destroyCritere(CritereEvaluation $critere)
    {
        $critere->delete();
        return back()->with('success', 'Critère supprimé.');
    }
}
