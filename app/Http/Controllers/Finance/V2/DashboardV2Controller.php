<?php

namespace App\Http\Controllers\Finance\V2;

use App\Http\Controllers\Controller;
use App\Models\Exercice;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetSource;
use App\Models\Finance\Modif;
use App\Models\Finance\Source;
use App\Models\Finance\Transaction;

/**
 * Dashboard Finance V2 — aligné au schéma initial OPTIMIZE Finance.
 */
class DashboardV2Controller extends Controller
{
    public function index()
    {
        $exercice = Exercice::whereRaw("COALESCE(status, 0) = 1 OR COALESCE(status, statut, 0) = 1")
            ->orWhere('statut', 2)
            ->orderByDesc('id')
            ->first();

        $kpis = [
            'nb_sources'      => Source::count(),
            'nb_budgets'      => Budget::count(),
            'nb_transactions' => Transaction::count(),
            'nb_modifs'       => Modif::count(),
        ];

        $totalBudgetsSources = 0;
        $repartitionSources = collect();
        if ($exercice) {
            $totalBudgetsSources = (float) BudgetSource::where('exercice_id', $exercice->id)->sum('montant');
            $repartitionSources = BudgetSource::where('exercice_id', $exercice->id)
                ->selectRaw('source_id, SUM(montant) as total')
                ->groupBy('source_id')
                ->with('source')
                ->get()
                ->map(fn($r) => [
                    'source' => $r->source?->label ?? $r->source?->code ?? '—',
                    'code'   => $r->source?->code ?? '—',
                    'total'  => (float) $r->total,
                    'pct'    => 0,
                ]);
            $sum = $repartitionSources->sum('total');
            if ($sum > 0) {
                $repartitionSources = $repartitionSources->map(function ($r) use ($sum) {
                    $r['pct'] = round($r['total'] / $sum * 100, 1);
                    return $r;
                });
            }
        }

        $dernieresTransactions = Transaction::with(['ligne', 'compte'])->latest()->limit(8)->get();
        $modifsEnAttente = Modif::whereIn('status', [1, 2])->orderByDesc('id')->limit(5)->get();

        return view('finance.v2.dashboard', compact(
            'exercice', 'kpis', 'totalBudgetsSources',
            'repartitionSources', 'dernieresTransactions', 'modifsEnAttente'
        ));
    }
}
