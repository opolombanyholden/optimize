<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeCritere;
use Illuminate\Http\Request;

/**
 * Gestion des grades (référentiel RH) et de leurs critères.
 */
class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::withCount(['criteres', 'employees'])
            ->ordonnes()
            ->get();

        return view('rh.grades.index', compact('grades'));
    }

    public function show(Grade $grade)
    {
        $grade->load(['criteres', 'employees:id,noms,prenoms,poste,departement']);
        return view('rh.grades.show', compact('grade'));
    }

    public function store(Request $request)
    {
        $data = $this->validateGrade($request);
        Grade::create($data);
        return redirect()->route('rh.grades.index')->with('success', 'Grade créé.');
    }

    public function update(Request $request, Grade $grade)
    {
        $data = $this->validateGrade($request, $grade);
        $grade->update($data);
        return back()->with('success', 'Grade mis à jour.');
    }

    public function destroy(Grade $grade)
    {
        if ($grade->employees()->exists() || $grade->avancements()->exists()) {
            return back()->with('error', 'Ce grade est utilisé (employés ou historique d\'avancement) — suppression bloquée.');
        }
        $grade->delete();
        return redirect()->route('rh.grades.index')->with('success', 'Grade supprimé.');
    }

    // ── CRITÈRES ────────────────────────────────────────────

    public function storeCritere(Request $request, Grade $grade)
    {
        $data = $this->validateCritere($request);
        $data['grade_id'] = $grade->id;
        GradeCritere::create($data);
        return back()->with('success', 'Critère ajouté.');
    }

    public function updateCritere(Request $request, Grade $grade, GradeCritere $critere)
    {
        abort_unless($critere->grade_id === $grade->id, 404);
        $critere->update($this->validateCritere($request));
        return back()->with('success', 'Critère modifié.');
    }

    public function destroyCritere(Grade $grade, GradeCritere $critere)
    {
        abort_unless($critere->grade_id === $grade->id, 404);
        $critere->delete();
        return back()->with('success', 'Critère supprimé.');
    }

    // ── HELPERS ─────────────────────────────────────────────

    private function validateGrade(Request $request, ?Grade $grade = null): array
    {
        $uniqueRule = $grade ? 'unique:grades,code,'.$grade->id : 'unique:grades,code';
        $notThisGrade = $grade ? "|not_in:{$grade->id}" : '';

        $data = $request->validate([
            'code'                    => "required|string|max:30|{$uniqueRule}",
            'libelle'                 => 'required|string|max:100',
            'description'             => 'nullable|string|max:2000',
            'ordre'                   => 'nullable|integer|min:0|max:999',
            'actif'                   => 'nullable|boolean',
            'avancement_automatique'  => 'nullable|boolean',
            'duree_max_mois'          => 'nullable|integer|min:1|max:600',
            'grade_suivant_id'        => "nullable|exists:grades,id{$notThisGrade}",
        ]);

        $data['actif']                  = (bool) $request->input('actif', true);
        $data['avancement_automatique'] = (bool) $request->input('avancement_automatique', false);
        $data['ordre']                  = (int) $request->input('ordre', 0);

        // Cohérence : si non-automatique, on nettoie les champs associés
        if (!$data['avancement_automatique']) {
            $data['duree_max_mois']   = null;
            $data['grade_suivant_id'] = null;
        }

        return $data;
    }

    private function validateCritere(Request $request): array
    {
        return $request->validate([
            'libelle'     => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'obligatoire' => 'nullable|boolean',
            'ordre'       => 'nullable|integer|min:0|max:999',
        ]) + ['obligatoire' => (bool) $request->input('obligatoire', true), 'ordre' => (int) $request->input('ordre', 0)];
    }
}
