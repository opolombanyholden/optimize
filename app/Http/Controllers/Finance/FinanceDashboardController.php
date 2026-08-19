<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\GrandLivre;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        // ─── EXERCICE EN COURS ──────────────────────────────
        $exerciceEnCours = Exercice::enCours()->first();
        $exerciceId = $exerciceEnCours?->id;

        // Exercices à valider (soumis pour top management)
        $exercicesSoumis = Exercice::soumis()->orderByDesc('soumis_at')->get();
        // Exercices en attente de clôture (date fin passée)
        $exercicesAttenteCloture = Exercice::enAttenteCloture()->get();

        // ─── KPIs PRINCIPAUX ────────────────────────────────
        $kpis = [
            'exercices_total'      => Exercice::count(),
            'exercices_en_cours'   => Exercice::enCours()->count(),
            'exercices_planifies'  => Exercice::planifie()->count(),
            'exercices_clotures'   => Exercice::cloture()->count(),
            'comptes_actifs'       => Compte::count(),
            'ecritures_mois'       => GrandLivre::whereMonth('date_ecriture', now()->month)
                                                ->whereYear('date_ecriture', now()->year)->count(),
            'ecritures_brouillons' => GrandLivre::where('isvalide', 0)->count(),
        ];

        // ─── EXÉCUTION BUDGÉTAIRE (sur exercice en cours) ───
        $budgetTotal = 0;
        $engagementTotal = 0;
        $tauxExecution = 0;
        $top5Lignes = collect();
        $lignesEnAlerte = collect();

        if ($exerciceId) {
            $lignes = BudgetLigne::where('id_exercicebudgetaire', $exerciceId)->get();
            $budgetTotal = $lignes->sum(fn($l) => $l->budget_total);
            $engagementTotal = (float) $lignes->sum('engagement');
            $tauxExecution = $budgetTotal > 0 ? round($engagementTotal / $budgetTotal * 100, 2) : 0;

            $top5Lignes = $lignes
                ->sortByDesc('engagement')
                ->take(5)
                ->map(fn($l) => [
                    'libelle'   => $l->commentaire ?: $l->id_budgetligne,
                    'budget'    => $l->budget_total,
                    'engage'    => (float) $l->engagement,
                    'taux'      => $l->budget_total > 0 ? round(($l->engagement ?? 0) / $l->budget_total * 100, 1) : 0,
                ])
                ->values();

            // Lignes en alerte : engagement > 90% du budget
            $lignesEnAlerte = $lignes
                ->filter(fn($l) => $l->budget_total > 0 && ($l->engagement ?? 0) / $l->budget_total >= 0.9)
                ->map(fn($l) => [
                    'id'        => $l->id,
                    'libelle'   => $l->commentaire ?: $l->id_budgetligne,
                    'budget'    => $l->budget_total,
                    'engage'    => (float) $l->engagement,
                    'taux'      => round(($l->engagement ?? 0) / $l->budget_total * 100, 1),
                    'depasse'   => ($l->engagement ?? 0) > $l->budget_total,
                ])
                ->values();
        }

        // ─── ÉVOLUTION DES ÉCRITURES SUR 12 MOIS ────────────
        $evolutionEcritures = collect(range(0, 11))->map(function ($i) {
            $d = Carbon::create(2026, 6, 1)->subMonths($i);
            $count = GrandLivre::whereYear('date_ecriture', $d->year)
                ->whereMonth('date_ecriture', $d->month)
                ->count();
            $montant = (float) GrandLivre::whereYear('date_ecriture', $d->year)
                ->whereMonth('date_ecriture', $d->month)
                ->where('sens', 'debit')
                ->sum('montant_tc');
            return [
                'mois'    => $d->translatedFormat('M Y'),
                'count'   => $count,
                'montant' => round($montant),
            ];
        })->reverse()->values();

        // ─── DERNIÈRES ÉCRITURES ────────────────────────────
        $dernieresEcritures = GrandLivre::with(['compte', 'user'])
            ->orderByDesc('date_ecriture')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('finance.dashboard', compact(
            'exerciceEnCours', 'kpis',
            'budgetTotal', 'engagementTotal', 'tauxExecution',
            'top5Lignes', 'lignesEnAlerte',
            'evolutionEcritures', 'dernieresEcritures',
            'exercicesSoumis', 'exercicesAttenteCloture'
        ));
    }
}
