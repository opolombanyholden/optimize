<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Avancement;
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
        $now       = now();
        $startMois = $now->copy()->startOfMonth();
        $moisPrec  = $now->copy()->subMonth();

        // ── EFFECTIF ──────────────────────────────────────
        $effectifActif   = Employee::where('statut', 1)->count();
        $embauchesMois   = Employee::where('statut', 1)->whereBetween('date_embauche', [$startMois, $now])->count();
        $departsMois     = Depart::where('statut', 1)
            ->whereBetween('created_at', [$startMois, $now])->count();

        // Contrats CDD arrivant à échéance sous 30 jours
        $cddProchainement = Employee::where('statut', 1)
            ->whereNotNull('date_fin_contrat')
            ->whereBetween('date_fin_contrat', [$now->copy()->startOfDay(), $now->copy()->addDays(30)->endOfDay()])
            ->with([])
            ->orderBy('date_fin_contrat')
            ->take(5)
            ->get();
        $nbCddProchainement = Employee::where('statut', 1)
            ->whereNotNull('date_fin_contrat')
            ->whereBetween('date_fin_contrat', [$now->copy()->startOfDay(), $now->copy()->addDays(30)->endOfDay()])
            ->count();

        // ── MASSE SALARIALE ───────────────────────────────
        $masseSalarialeMois = (float) Paie::whereBetween('debut', [$startMois, $now])
            ->whereIn('statut', [1, 2])
            ->sum('net_a_payer');
        $masseSalarialeMoisPrec = (float) Paie::whereBetween('debut', [
                $moisPrec->copy()->startOfMonth(), $moisPrec->copy()->endOfMonth()
            ])->whereIn('statut', [1, 2])->sum('net_a_payer');
        $tendanceMasse = $masseSalarialeMoisPrec > 0
            ? round((($masseSalarialeMois - $masseSalarialeMoisPrec) / $masseSalarialeMoisPrec) * 100)
            : ($masseSalarialeMois > 0 ? 100 : 0);

        // Sparkline masse 6 mois
        $masseSix = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $brut = (float) Paie::whereYear('debut', $d->year)->whereMonth('debut', $d->month)
                ->whereIn('statut', [1, 2])->sum('net_a_payer');
            $masseSix[] = [
                'label'   => $d->locale('fr')->isoFormat('MMM'),
                'montant' => $brut,
            ];
        }
        $masseMax = max(array_column($masseSix, 'montant')) ?: 1;

        // ── ABSENCES ──────────────────────────────────────
        $absencesEnAttente     = Absence::where('statut', 0)->count();
        $absencesRetardApprobation = Absence::where('statut', 0)
            ->where('created_at', '<=', $now->copy()->subDays(3))->count();
        $absencesEnCours       = Absence::whereIn('statut', [1])
            ->whereDate('debut', '<=', $now)
            ->whereDate('fin', '>=', $now)
            ->count();
        $dernieresAbsences     = Absence::with(['employee'])
            ->where('statut', 0)
            ->latest()->take(5)->get();

        // ── PAIE ──────────────────────────────────────────
        $bulletinsMois       = Paie::whereBetween('debut', [$startMois, $now])->count();
        $bulletinsMoisPrec   = Paie::whereBetween('debut', [
                $moisPrec->copy()->startOfMonth(), $moisPrec->copy()->endOfMonth()
            ])->count();
        $tendanceBulletins = $bulletinsMoisPrec > 0
            ? round((($bulletinsMois - $bulletinsMoisPrec) / $bulletinsMoisPrec) * 100)
            : ($bulletinsMois > 0 ? 100 : 0);
        $bulletinsBrouillon  = Paie::where('statut', 0)->count();

        // ── RÉPARTITION CONTRATS (donut) ──────────────────
        $repartitionContrats = Employee::where('statut', 1)
            ->selectRaw("COALESCE(NULLIF(TRIM(type_contrat), ''), 'Non renseigné') as type_contrat, count(*) as n")
            ->groupBy('type_contrat')
            ->orderByDesc('n')
            ->pluck('n', 'type_contrat')
            ->toArray();
        $totalContrats = array_sum($repartitionContrats) ?: 1;

        // ── TOP DÉPARTEMENTS (podium) ─────────────────────
        $topDepartements = Employee::where('statut', 1)
            ->selectRaw("COALESCE(NULLIF(TRIM(departement), ''), 'Non renseigné') as departement, count(*) as n")
            ->groupBy('departement')
            ->orderByDesc('n')
            ->limit(5)
            ->get();
        $topDeptMax = $topDepartements->max('n') ?: 1;

        // ── ACTIVITÉ ──────────────────────────────────────
        $sanctionsActives     = Sanction::where('statut', 1)->count();
        $departsEnCours       = Depart::where('statut', 1)->count();
        $evaluationsEnCours   = EvaluationPerformance::whereIn('statut', [0, 1])->count();
        $missionsActives      = Mission::where('statut', 1)->count();
        $recrutementsOuverts  = Recrutement::where('statut', 0)->count();

        $derniersEmbauches = Employee::where('statut', 1)
            ->whereNotNull('date_embauche')
            ->orderByDesc('date_embauche')
            ->take(5)
            ->get(['id', 'noms', 'prenoms', 'poste', 'departement', 'date_embauche', 'type_contrat']);

        // ── ANNIVERSAIRES DU MOIS ─────────────────────────
        $anniversairesMois = Employee::where('statut', 1)
            ->whereNotNull('date_naissance')
            ->whereRaw("EXTRACT(MONTH FROM date_naissance) = ?", [$now->month])
            ->orderByRaw("EXTRACT(DAY FROM date_naissance)")
            ->get(['id', 'noms', 'prenoms', 'date_naissance', 'poste']);

        // ── AVANCEMENTS AUTOMATIQUES EN ATTENTE ────────────
        $avancementsAttente = Avancement::proposes()->automatiques()
            ->with(['employee:id,noms,prenoms,poste,departement,date_embauche', 'grade:id,code,libelle', 'gradePrecedent:id,code,libelle'])
            ->orderByDesc('date_effet')
            ->take(10)
            ->get();
        $nbAvancementsAttente = Avancement::proposes()->automatiques()->count();

        return view('rh.dashboard', compact(
            'effectifActif', 'embauchesMois', 'departsMois',
            'cddProchainement', 'nbCddProchainement',
            'masseSalarialeMois', 'masseSalarialeMoisPrec', 'tendanceMasse',
            'masseSix', 'masseMax',
            'absencesEnAttente', 'absencesRetardApprobation', 'absencesEnCours', 'dernieresAbsences',
            'bulletinsMois', 'bulletinsMoisPrec', 'tendanceBulletins', 'bulletinsBrouillon',
            'repartitionContrats', 'totalContrats',
            'topDepartements', 'topDeptMax',
            'sanctionsActives', 'departsEnCours', 'evaluationsEnCours', 'missionsActives', 'recrutementsOuverts',
            'derniersEmbauches', 'anniversairesMois',
            'avancementsAttente', 'nbAvancementsAttente'
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
