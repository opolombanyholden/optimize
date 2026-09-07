<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Avancement;
use App\Models\Employee;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvancementController extends Controller
{
    public function index(Employee $employee)
    {
        $employee->load(['grade', 'avancements.grade', 'avancements.gradePrecedent', 'avancements.decideur']);
        $grades = Grade::actifs()->ordonnes()->get();
        return view('rh.avancements.index', compact('employee', 'grades'));
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'grade_id'           => 'required|exists:grades,id',
            'date_effet'         => 'required|date',
            'motif'              => 'nullable|string|max:255',
            'reference_document' => 'nullable|string|max:100',
            'commentaire'        => 'nullable|string|max:2000',
        ]);

        if ($employee->grade_id && (int) $data['grade_id'] === (int) $employee->grade_id) {
            return back()->with('error', 'L\'employé possède déjà ce grade.')->withInput();
        }

        Avancement::create(array_merge($data, [
            'employee_id' => $employee->id,
            'decide_par'  => auth()->id(),
            'origine'     => Avancement::ORIGINE_MANUEL,
        ]));

        return redirect()->route('rh.employees.avancements.index', $employee)
            ->with('success', 'Avancement enregistré.');
    }

    public function destroy(Employee $employee, Avancement $avancement)
    {
        abort_unless($avancement->employee_id === $employee->id, 404);
        $avancement->delete();

        // Recalcul du grade actuel : dernier avancement VALIDÉ restant
        $dernier = $employee->avancements()->valides()->orderByDesc('date_effet')->orderByDesc('id')->first();
        $employee->update(['grade_id' => $dernier?->grade_id]);

        return back()->with('success', 'Entrée d\'avancement supprimée. Grade actuel recalculé.');
    }

    /**
     * Détecte les employés éligibles à un avancement AUTOMATIQUE (durée max atteinte)
     * et crée les entrées 'propose' correspondantes. Idempotent.
     */
    public function detecterAutomatiques(Request $request)
    {
        $crees = 0;
        $today = now()->startOfDay();

        $gradesAuto = Grade::actifs()->automatiques()->get();
        foreach ($gradesAuto as $grade) {
            $cible = $grade->grade_cible_auto();
            if (!$cible) continue; // pas de grade cible → skip

            $employees = Employee::where('statut', 1)
                ->where('grade_id', $grade->id)
                ->get();

            foreach ($employees as $emp) {
                // Date de référence : dernier avancement validé vers ce grade, sinon date_embauche
                $ref = $emp->avancements()
                    ->valides()
                    ->where('grade_id', $grade->id)
                    ->orderByDesc('date_effet')->orderByDesc('id')
                    ->first();

                $depuis = $ref?->date_effet ?? ($emp->date_embauche ? Carbon::parse($emp->date_embauche) : null);
                if (!$depuis) continue;

                $moisEcoules = $depuis->startOfDay()->diffInMonths($today);
                if ($moisEcoules < $grade->duree_max_mois) continue;

                // Un 'propose' existe déjà pour ce couple ? → skip (idempotence)
                $existe = Avancement::proposes()
                    ->where('employee_id', $emp->id)
                    ->where('grade_id', $cible->id)
                    ->exists();
                if ($existe) continue;

                Avancement::create([
                    'employee_id'        => $emp->id,
                    'grade_id'           => $cible->id,
                    'grade_precedent_id' => $grade->id,
                    'date_effet'         => $today,
                    'motif'              => "Avancement automatique après {$moisEcoules} mois au grade {$grade->libelle}",
                    'origine'            => Avancement::ORIGINE_AUTOMATIQUE,
                    'statut'             => Avancement::STATUT_PROPOSE,
                    'decide_par'         => null,
                ]);
                $crees++;
            }
        }

        return back()->with('success', $crees > 0
            ? "$crees avancement(s) automatique(s) détecté(s) et proposé(s) à la validation."
            : "Aucun nouvel avancement automatique à proposer.");
    }

    public function valider(Request $request, Avancement $avancement)
    {
        abort_unless($avancement->statut === Avancement::STATUT_PROPOSE, 422, 'Avancement déjà traité.');

        $avancement->update([
            'statut'     => Avancement::STATUT_VALIDE,
            'valide_par' => auth()->id(),
            'valide_at'  => now(),
        ]);

        // Sync grade actuel de l'employé
        Employee::where('id', $avancement->employee_id)->update(['grade_id' => $avancement->grade_id]);

        return back()->with('success', 'Avancement validé. Le grade de l\'employé a été mis à jour.');
    }

    public function refuser(Request $request, Avancement $avancement)
    {
        abort_unless($avancement->statut === Avancement::STATUT_PROPOSE, 422, 'Avancement déjà traité.');
        $data = $request->validate(['motif_refus' => 'required|string|min:5|max:500']);

        $avancement->update([
            'statut'      => Avancement::STATUT_REFUSE,
            'valide_par'  => auth()->id(),
            'valide_at'   => now(),
            'motif_refus' => $data['motif_refus'],
        ]);

        return back()->with('success', 'Avancement refusé. L\'employé conserve son grade actuel.');
    }
}
