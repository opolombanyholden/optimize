<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\GrandLivre;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $now       = now();
        $startMois = $now->copy()->startOfMonth();
        $moisPrec  = $now->copy()->subMonth();

        // ── EXERCICE EN COURS ───────────────────────────────
        $exerciceEnCours          = Exercice::enCours()->first();
        $exerciceId               = $exerciceEnCours?->id;
        $exercicesSoumis          = Exercice::soumis()->orderByDesc('soumis_at')->get();
        $exercicesAttenteCloture  = Exercice::enAttenteCloture()->get();

        // ── EXÉCUTION BUDGÉTAIRE (exercice en cours) ────────
        $budgetTotal      = 0;
        $engagementTotal  = 0;
        $tauxExecution    = 0;
        $resteAEngager    = 0;
        $top5Lignes       = collect();
        $lignesEnAlerte   = collect();
        $lignesDepassees  = collect();

        if ($exerciceId) {
            $lignes = BudgetLigne::where('id_exercicebudgetaire', $exerciceId)->get();
            $budgetTotal     = (float) $lignes->sum(fn($l) => $l->budget_total);
            $engagementTotal = (float) $lignes->sum('engagement');
            $tauxExecution   = $budgetTotal > 0 ? round($engagementTotal / $budgetTotal * 100, 1) : 0;
            $resteAEngager   = max(0, $budgetTotal - $engagementTotal);

            $top5Lignes = $lignes
                ->sortByDesc('engagement')
                ->take(5)
                ->map(fn($l) => [
                    'libelle' => $l->commentaire ?: 'Ligne #'.$l->id_budgetligne,
                    'budget'  => (float) $l->budget_total,
                    'engage'  => (float) $l->engagement,
                    'taux'    => $l->budget_total > 0 ? round(($l->engagement ?? 0) / $l->budget_total * 100, 1) : 0,
                ])
                ->values();
            $top5LignesMax = $top5Lignes->max('engage') ?: 1;

            $lignesEnAlerte = $lignes
                ->filter(fn($l) => $l->budget_total > 0 && ($l->engagement ?? 0) / $l->budget_total >= 0.9 && ($l->engagement ?? 0) <= $l->budget_total)
                ->map(fn($l) => [
                    'id'      => $l->id ?? $l->id_budgetligne,
                    'libelle' => $l->commentaire ?: 'Ligne #'.$l->id_budgetligne,
                    'budget'  => (float) $l->budget_total,
                    'engage'  => (float) $l->engagement,
                    'taux'    => round(($l->engagement ?? 0) / $l->budget_total * 100, 1),
                ])
                ->values();

            $lignesDepassees = $lignes
                ->filter(fn($l) => $l->budget_total > 0 && ($l->engagement ?? 0) > $l->budget_total)
                ->map(fn($l) => [
                    'id'          => $l->id ?? $l->id_budgetligne,
                    'libelle'     => $l->commentaire ?: 'Ligne #'.$l->id_budgetligne,
                    'budget'      => (float) $l->budget_total,
                    'engage'      => (float) $l->engagement,
                    'depassement' => (float) $l->engagement - (float) $l->budget_total,
                ])
                ->values();
        } else {
            $top5LignesMax = 1;
        }

        // ── FACTURES : créances clients & dettes fournisseurs ────
        $facturesEnCours = Facture::whereIn('statut', [1, 2]);   // Validée + Partiellement réglée

        $creancesClients = (float) (clone $facturesEnCours)
            ->where('sens', 'recette')
            ->selectRaw('COALESCE(SUM(montant_ttc - COALESCE(montant_regle, 0)), 0) as v')
            ->value('v');
        $dettesFournisseurs = (float) (clone $facturesEnCours)
            ->where('sens', 'depense')
            ->selectRaw('COALESCE(SUM(montant_ttc - COALESCE(montant_regle, 0)), 0) as v')
            ->value('v');

        // Factures échues (date_echeance < today, non entièrement réglées)
        $facturesEchuesQ = Facture::whereIn('statut', [1, 2])
            ->whereNotNull('date_echeance')
            ->whereDate('date_echeance', '<', $now)
            ->whereRaw('COALESCE(montant_regle, 0) < COALESCE(montant_ttc, 0)');

        $nbFacturesEchues     = (clone $facturesEchuesQ)->count();
        $montantFacturesEchues = (float) (clone $facturesEchuesQ)
            ->selectRaw('COALESCE(SUM(montant_ttc - COALESCE(montant_regle,0)), 0) as v')->value('v');
        $facturesEchuesListe   = (clone $facturesEchuesQ)
            ->orderBy('date_echeance')->take(5)->get();

        // Prochaines échéances (7 prochains jours)
        $facturesProchainesEcheances = Facture::whereIn('statut', [1, 2])
            ->whereNotNull('date_echeance')
            ->whereBetween('date_echeance', [$now->copy()->startOfDay(), $now->copy()->addDays(7)->endOfDay()])
            ->whereRaw('COALESCE(montant_regle, 0) < COALESCE(montant_ttc, 0)')
            ->count();

        // ── ÉVOLUTION MENSUELLE (6 mois) — écritures débit ──
        $depensesMois = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $montant = (float) GrandLivre::whereYear('date_ecriture', $d->year)
                ->whereMonth('date_ecriture', $d->month)
                ->where('sens', 'debit')
                ->sum('montant_tc');
            $depensesMois[] = [
                'label'   => $d->locale('fr')->isoFormat('MMM'),
                'montant' => $montant,
            ];
        }
        $depensesMax = max(array_column($depensesMois, 'montant')) ?: 1;

        // ── TENDANCE : ce mois vs mois précédent (débits comptables) ──
        $montantDebitMois = (float) GrandLivre::whereBetween('date_ecriture', [$startMois, $now])
            ->where('sens', 'debit')->sum('montant_tc');
        $montantDebitMoisPrec = (float) GrandLivre::whereBetween('date_ecriture', [
                $moisPrec->copy()->startOfMonth(), $moisPrec->copy()->endOfMonth()
            ])->where('sens', 'debit')->sum('montant_tc');
        $tendanceDebit = $montantDebitMoisPrec > 0
            ? round((($montantDebitMois - $montantDebitMoisPrec) / $montantDebitMoisPrec) * 100)
            : ($montantDebitMois > 0 ? 100 : 0);

        // ── ÉCRITURES : brouillons + activité ce mois ────────
        $ecrituresMois       = GrandLivre::whereBetween('date_ecriture', [$startMois, $now])->count();
        $ecrituresMoisPrec   = GrandLivre::whereBetween('date_ecriture', [
                $moisPrec->copy()->startOfMonth(), $moisPrec->copy()->endOfMonth()
            ])->count();
        $tendanceEcritures = $ecrituresMoisPrec > 0
            ? round((($ecrituresMois - $ecrituresMoisPrec) / $ecrituresMoisPrec) * 100)
            : ($ecrituresMois > 0 ? 100 : 0);
        $ecrituresBrouillon = GrandLivre::where('isvalide', 0)->count();

        // ── TOP CLIENTS/FOURNISSEURS (12 mois) — via factures ──
        $topClients = Facture::selectRaw("
                COALESCE(tiers_id, 0) as tiers_id,
                tiers_source,
                SUM(montant_ttc) as total,
                COUNT(*) as nb
            ")
            ->where('sens', 'recette')
            ->where('statut', '!=', 4)
            ->whereBetween('date_emission', [$now->copy()->subMonths(12), $now])
            ->whereNotNull('tiers_id')
            ->groupBy('tiers_id', 'tiers_source')
            ->orderByDesc('total')
            ->take(5)
            ->get();
        $topClientsMax = $topClients->max('total') ?: 1;

        // ── DERNIÈRES ÉCRITURES ─────────────────────────────
        $dernieresEcritures = GrandLivre::with(['compte'])
            ->orderByDesc('date_ecriture')->orderByDesc('id')->limit(6)->get();

        // ── COMPTES ─────────────────────────────────────────
        $comptesTotal = Compte::count();

        return view('finance.dashboard', compact(
            'exerciceEnCours', 'exercicesSoumis', 'exercicesAttenteCloture',
            'budgetTotal', 'engagementTotal', 'tauxExecution', 'resteAEngager',
            'top5Lignes', 'top5LignesMax', 'lignesEnAlerte', 'lignesDepassees',
            'creancesClients', 'dettesFournisseurs',
            'nbFacturesEchues', 'montantFacturesEchues', 'facturesEchuesListe',
            'facturesProchainesEcheances',
            'depensesMois', 'depensesMax',
            'montantDebitMois', 'tendanceDebit',
            'ecrituresMois', 'tendanceEcritures', 'ecrituresBrouillon',
            'topClients', 'topClientsMax',
            'dernieresEcritures',
            'comptesTotal'
        ));
    }
}
