<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\Paie;
use App\Models\PayementGlobal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayementGlobalController extends Controller
{
    public function index(Request $request)
    {
        $annee = (int) ($request->annee ?? now()->year);

        $globals = PayementGlobal::where('annee', $annee)
            ->orderBy('mois')
            ->get();

        return view('rh.payements-globals.index', compact('globals', 'annee'));
    }

    public function show(PayementGlobal $payements_global)
    {
        $payements_global->load(['cloturePar', 'exercice']);
        return view('rh.payements-globals.show', ['global' => $payements_global]);
    }

    /**
     * Recalcule l'agrégat (masse salariale) pour un mois donné.
     */
    public function recalculer(Request $request)
    {
        $data = $request->validate([
            'annee' => ['required', 'integer', 'min:2020', 'max:2100'],
            'mois'  => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $debut = Carbon::create($data['annee'], $data['mois'], 1)->startOfMonth();
        $fin   = (clone $debut)->endOfMonth();

        $stats = Paie::query()
            ->whereBetween('debut', [$debut, $fin])
            ->whereIn('statut', [1, 2])
            ->selectRaw('
                COUNT(*) as nombre_bulletins,
                COALESCE(SUM(brut),0) as masse_salariale_brute,
                COALESCE(SUM(net_a_payer),0) as masse_salariale_nette,
                COALESCE(SUM(cotisations_salariales),0) as total_cotisations_salariales,
                COALESCE(SUM(cotisations_patronales),0) as total_cotisations_patronales,
                COALESCE(SUM(irpp),0) as total_irpp,
                COALESCE(SUM(avances),0) as total_avances,
                COALESCE(SUM(retenues),0) as total_retenues
            ')
            ->first()
            ->toArray();

        // Agrégat par département
        $parDept = Paie::query()
            ->whereBetween('gpaies.debut', [$debut, $fin])
            ->whereIn('gpaies.statut', [1, 2])
            ->join('employees', 'gpaies.employee_id', '=', 'employees.id')
            ->selectRaw('employees.departement, COUNT(*) as nb, SUM(gpaies.brut) as brut, SUM(gpaies.net_a_payer) as net')
            ->groupBy('employees.departement')
            ->get()
            ->keyBy('departement')
            ->toArray();

        // Total payé
        $totalPaye = DB::table('payements')
            ->join('gpaies', 'payements.paie_id', '=', 'gpaies.id')
            ->whereBetween('gpaies.debut', [$debut, $fin])
            ->where('payements.statut', 1)
            ->sum('payements.montant');

        $global = PayementGlobal::updateOrCreate(
            ['annee' => $data['annee'], 'mois' => $data['mois']],
            array_merge($stats, [
                'total_paye'              => $totalPaye,
                'agregats_par_departement' => $parDept,
                'statut'                  => 0,
            ])
        );

        return redirect()->route('rh.payements-globals.show', $global)
            ->with('success', 'Agregats recalcules pour ' . $debut->translatedFormat('F Y'));
    }

    public function cloturer(PayementGlobal $payements_global)
    {
        if ($payements_global->statut == 1) {
            return back()->with('error', 'Cette periode est deja cloturee.');
        }
        $payements_global->update([
            'statut'        => 1,
            'cloturee_par'  => auth()->id(),
            'cloturee_at'   => now(),
        ]);
        return back()->with('success', 'Periode cloturee.');
    }
}
