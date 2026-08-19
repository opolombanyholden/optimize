<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Pointage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointageController extends Controller
{
    public function index(Request $request)
    {
        $mois  = (int) ($request->mois ?: now()->month);
        $annee = (int) ($request->annee ?: now()->year);
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = (clone $debut)->endOfMonth();

        $pointages = Pointage::query()
            ->with(['employee', 'saisiPar'])
            ->pourPeriode($debut->toDateString(), $fin->toDateString())
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderByDesc('date')
            ->orderBy('employee_id')
            ->paginate(50)
            ->withQueryString();

        $employees = Employee::where('statut', 1)->orderBy('noms')->get(['id', 'noms', 'prenoms', 'matricule']);

        return view('rh.pointages.index', compact('pointages', 'employees', 'mois', 'annee'));
    }

    public function create()
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.pointages.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePointage($request);

        // Upsert : si un pointage existe déjà pour cet employé/date, on update
        $pointage = Pointage::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date' => $data['date']],
            array_merge($data, [
                'saisi_par' => auth()->id(),
                'statut'    => $data['statut'] ?? 0,
            ])
        );

        return redirect()->route('rh.pointages.index', ['employee_id' => $data['employee_id']])
            ->with('success', 'Pointage enregistré.');
    }

    public function show(Pointage $pointage)
    {
        $pointage->load(['employee', 'saisiPar', 'validePar']);
        return view('rh.pointages.show', compact('pointage'));
    }

    public function edit(Pointage $pointage)
    {
        abort_if(!$pointage->est_modifiable, 403, 'Ce pointage est verrouillé.');
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        return view('rh.pointages.edit', compact('pointage', 'employees'));
    }

    public function update(Request $request, Pointage $pointage)
    {
        abort_if(!$pointage->est_modifiable, 403, 'Ce pointage est verrouillé.');
        $data = $this->validatePointage($request);
        $pointage->update($data);
        return redirect()->route('rh.pointages.index')->with('success', 'Pointage mis à jour.');
    }

    public function destroy(Pointage $pointage)
    {
        abort_if(!$pointage->est_modifiable, 403, 'Ce pointage est verrouillé.');
        $pointage->delete();
        return redirect()->route('rh.pointages.index')->with('success', 'Pointage supprimé.');
    }

    /**
     * Valide un ou plusieurs pointages (passe statut 0 → 1).
     */
    public function valider(Request $request)
    {
        $data = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:pointages,id']);
        $count = Pointage::whereIn('id', $data['ids'])->where('statut', 0)->update([
            'statut'     => 1,
            'valide_par' => auth()->id(),
            'valide_at'  => now(),
        ]);
        return back()->with('success', "$count pointage(s) validé(s).");
    }

    /**
     * Saisie en grille : un employé × tous les jours d'un mois.
     */
    public function grilleEmploye(Request $request, Employee $employee)
    {
        $mois  = (int) ($request->mois ?: now()->month);
        $annee = (int) ($request->annee ?: now()->year);
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin   = (clone $debut)->endOfMonth();

        $pointages = Pointage::where('employee_id', $employee->id)
            ->whereBetween('date', [$debut, $fin])
            ->get()
            ->keyBy(fn($p) => $p->date->toDateString());

        $jours = [];
        for ($d = clone $debut; $d <= $fin; $d->addDay()) {
            $jours[] = (clone $d);
        }

        return view('rh.pointages.grille', compact('employee', 'pointages', 'jours', 'mois', 'annee'));
    }

    /**
     * Enregistre une grille (tous les jours d'un mois) pour un employé.
     */
    public function grilleEmployeStore(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'mois'              => 'required|integer|min:1|max:12',
            'annee'             => 'required|integer|min:2020|max:2100',
            'jours'             => 'required|array',
            'jours.*.date'      => 'required|date',
            'jours.*.h_normales'=> 'nullable|numeric|min:0|max:24',
            'jours.*.h_sup'     => 'nullable|numeric|min:0|max:24',
            'jours.*.h_nuit'    => 'nullable|numeric|min:0|max:24',
            'jours.*.h_dimanche'=> 'nullable|numeric|min:0|max:24',
        ]);

        $count = 0;
        DB::transaction(function () use ($data, $employee, &$count) {
            foreach ($data['jours'] as $jour) {
                // Skip lignes vides
                $total = ($jour['h_normales'] ?? 0) + ($jour['h_sup'] ?? 0)
                       + ($jour['h_nuit'] ?? 0) + ($jour['h_dimanche'] ?? 0);
                if ($total <= 0) {
                    // Si une ligne existe et qu'on remet tout à 0, on la supprime (seulement si brouillon)
                    Pointage::where('employee_id', $employee->id)
                        ->where('date', $jour['date'])
                        ->where('statut', 0)
                        ->delete();
                    continue;
                }
                Pointage::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $jour['date']],
                    [
                        'h_normales' => $jour['h_normales'] ?? 0,
                        'h_sup'      => $jour['h_sup'] ?? 0,
                        'h_nuit'     => $jour['h_nuit'] ?? 0,
                        'h_dimanche' => $jour['h_dimanche'] ?? 0,
                        'saisi_par'  => auth()->id(),
                    ]
                );
                $count++;
            }
        });

        return redirect()->route('rh.pointages.grille-employe', [
            'employee' => $employee->id,
            'mois'     => $data['mois'],
            'annee'    => $data['annee'],
        ])->with('success', "$count jour(s) enregistré(s) pour {$employee->noms} {$employee->prenoms}.");
    }

    private function validatePointage(Request $request): array
    {
        return $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'h_normales'  => 'nullable|numeric|min:0|max:24',
            'h_sup'       => 'nullable|numeric|min:0|max:24',
            'h_nuit'      => 'nullable|numeric|min:0|max:24',
            'h_dimanche'  => 'nullable|numeric|min:0|max:24',
            'motif'       => 'nullable|string|max:100',
            'notes'       => 'nullable|string|max:500',
            'statut'      => 'nullable|integer|in:0,1',
        ]);
    }
}
