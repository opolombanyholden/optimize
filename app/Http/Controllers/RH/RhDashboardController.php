<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Depart;
use App\Models\Employee;
use App\Models\EvaluationPerformance;
use App\Models\Mission;
use App\Models\Paie;
use App\Models\Payement;
use App\Models\Recrutement;
use App\Models\Sanction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class RhDashboardController extends Controller
{
    public function index()
    {
        $kpis = [
            'employes_actifs'     => Employee::where('statut', 1)->count(),
            'absences_en_attente' => Absence::where('statut', 0)->count(),
            'bulletins_mois'      => Paie::whereMonth('debut', now()->month)->whereYear('debut', now()->year)->count(),
            'recrutements_ouverts'=> Recrutement::where('statut', 0)->count(),
            'sanctions_actives'   => Sanction::where('statut', 1)->count(),
            'departs_en_cours'    => Depart::where('statut', 1)->count(),
            'evaluations_en_cours'=> EvaluationPerformance::whereIn('statut', [0, 1])->count(),
            'missions_actives'    => Mission::where('statut', 1)->count(),
        ];

        $masseSalarialeMois = Paie::whereMonth('debut', now()->month)
            ->whereYear('debut', now()->year)
            ->whereIn('statut', [1, 2])
            ->sum('net_a_payer');

        $repartitionContrats = Employee::where('statut', 1)
            ->selectRaw('type_contrat, count(*) as n')
            ->groupBy('type_contrat')
            ->pluck('n', 'type_contrat')
            ->toArray();

        $effectifParDept = Employee::where('statut', 1)
            ->selectRaw('departement, count(*) as n')
            ->groupBy('departement')
            ->orderByDesc('n')
            ->limit(10)
            ->pluck('n', 'departement')
            ->toArray();

        $derniereActivite = Activity::query()
            ->whereIn('log_name', ['employee', 'paie', 'payement', 'sanction', 'depart'])
            ->latest()
            ->limit(10)
            ->get();

        // Évolution masse salariale 12 derniers mois
        $evolutionMasse = collect(range(0, 11))->map(function ($i) {
            $d = Carbon::now()->subMonths($i);
            $brut = (float) Paie::whereYear('debut', $d->year)->whereMonth('debut', $d->month)
                ->whereIn('statut', [1, 2])->sum('brut');
            return ['mois' => $d->format('M Y'), 'brut' => round($brut)];
        })->reverse()->values();

        return view('rh.dashboard', compact(
            'kpis', 'masseSalarialeMois', 'repartitionContrats',
            'effectifParDept', 'derniereActivite', 'evolutionMasse'
        ));
    }

    /**
     * Journal d'audit RH (paie/payement/employee/sanction/depart).
     */
    public function auditLog(Request $request)
    {
        $logs = Activity::query()
            ->whereIn('log_name', ['employee', 'paie', 'payement', 'sanction', 'depart'])
            ->when($request->log_name, fn($q, $l) => $q->where('log_name', $l))
            ->when($request->subject_id, fn($q, $id) => $q->where('subject_id', $id))
            ->when($request->event, fn($q, $e) => $q->where('event', $e))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('rh.audit-log', compact('logs'));
    }

    /**
     * Export RGPD : récupère toutes les données personnelles d'un employé.
     */
    public function exportRgpd(Employee $employee)
    {
        $employee->load([
            'affilies', 'absences', 'competences', 'qualifications',
            'formations', 'paies', 'missions', 'evenementsCarriere',
        ]);

        $data = [
            'employee'         => $employee->toArray(),
            'affilies'         => $employee->affilies->toArray(),
            'absences'         => $employee->absences->toArray(),
            'competences'      => $employee->competences->toArray(),
            'qualifications'   => $employee->qualifications->toArray(),
            'formations'       => $employee->formations->toArray(),
            'paies'            => $employee->paies->toArray(),
            'missions'         => $employee->missions->toArray(),
            'evenementsCarriere' => $employee->evenementsCarriere->toArray(),
            'sanctions'        => Sanction::where('employee_id', $employee->id)->get()->toArray(),
            'departs'          => Depart::where('employee_id', $employee->id)->get()->toArray(),
            'evaluations'      => EvaluationPerformance::where('employee_id', $employee->id)->get()->toArray(),
            'payements'        => Payement::where('employee_id', $employee->id)->get()->toArray(),
            'exporte_le'       => now()->toIso8601String(),
            'exporte_par'      => auth()->user()->name,
        ];

        $filename = sprintf('rgpd_%s_%s.json',
            \Illuminate\Support\Str::slug($employee->matricule ?? $employee->id),
            now()->format('Y-m-d_His')
        );

        return response()
            ->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    /**
     * Anonymisation RGPD : remplace les PII de l'employé tout en gardant l'historique paie.
     * Réversible UNIQUEMENT avant clôture comptable de l'exercice.
     */
    public function anonymiserRgpd(Request $request, Employee $employee)
    {
        $request->validate([
            'confirmation' => ['required', 'in:ANONYMISER'],
            'motif'        => ['required', 'string', 'min:10', 'max:255'],
        ]);

        DB::transaction(function () use ($employee, $request) {
            $anon = 'ANON-' . str_pad((string) $employee->id, 6, '0', STR_PAD_LEFT);
            $employee->update([
                'noms'                  => $anon,
                'prenoms'               => 'Supprimé',
                'matricule'             => $anon,
                'email'                 => null,
                'contact'               => null,
                'adresse'               => null,
                'lieu_naissance'        => null,
                'date_naissance'        => null,
                'numero_secu'           => null,
                'nip'                   => null,
                'iban'                  => null,
                'pays'                  => null,
                'province'              => null,
                'departement_geo'       => null,
                'prefecture'            => null,
                'sous_prefecture'       => null,
                'zone_type'             => null,
                'commune'               => null,
                'arrondissement'        => null,
                'quartier_loc'          => null,
                'canton'                => null,
                'regroupement_village'  => null,
                'village'               => null,
                'situation_matrimoniale'=> null,
                'sexe'                  => null,
                'nationalite'           => null,
                'statut'                => 3,
                'extra_attributes'      => array_merge(
                    (array) $employee->extra_attributes,
                    [
                        'rgpd_anonymized_at' => now()->toIso8601String(),
                        'rgpd_anonymized_by' => auth()->user()->name,
                        'rgpd_motif'         => $request->motif,
                    ]
                ),
            ]);

            // Log explicite
            activity('rgpd')
                ->causedBy(auth()->user())
                ->performedOn($employee)
                ->withProperties(['motif' => $request->motif])
                ->log("Anonymisation RGPD de l'employé #{$employee->id}");
        });

        return redirect()->route('rh.employees.index')
            ->with('success', 'Employé anonymisé conformément au RGPD. Historique paie conservé.');
    }
}
