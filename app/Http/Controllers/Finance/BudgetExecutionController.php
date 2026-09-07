<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\ModificationBudgetaire;
use App\Models\Titre;
use Illuminate\Http\Request;

/**
 * Dashboard d'exécution budgétaire — par ligne :
 *   Planification (dotation_etat + fonds_propres + reports) → Modifications → Budget final →
 *   Consommation (engagement) → Solde disponible → Taux d'exécution.
 */
class BudgetExecutionController extends Controller
{
    public function index(Request $request)
    {
        // ── Exercice sélectionné : param ?exercice ou "en cours" par défaut
        $exercicesDisponibles = Exercice::orderByDesc('id')->get(['id', 'libelle', 'exercice', 'statut']);
        $exerciceId = (int) $request->query('exercice', 0)
            ?: (Exercice::enCours()->first()?->id ?? $exercicesDisponibles->first()?->id);
        $exercice = Exercice::find($exerciceId);

        // ── Filtres UI
        $filtreTitre = $request->query('titre');       // id_famillecodeanalytique
        $filtreEtat  = $request->query('etat');        // ok | alerte | depasse | epuise
        $filtreQ     = trim((string) $request->query('q', ''));

        // ── Lignes budgétaires
        $lignes = BudgetLigne::with(['titre:id,libelle,imputation'])
            ->where('id_exercicebudgetaire', $exerciceId)
            ->nonRetirees()
            ->when($filtreTitre, fn($q, $t) => $q->where('id_famillecodeanalytique', $t))
            ->when($filtreQ, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('budgetligne', 'ilike', "%{$s}%")
                  ->orWhere('commentaire', 'ilike', "%{$s}%")
                  ->orWhere('codecompte', 'ilike', "%{$s}%");
            }))
            ->orderBy('codecompte')
            ->orderBy('id')
            ->get();

        // ── Enrichissement par ligne (colonnes calculées + filtre état)
        $rows = $lignes->map(function ($l) {
            // Décomposition « Fond propre » (fonds internes + reports) vs « Fond alloué » (dotation État)
            $fondPropre    = (float) ($l->fonds_propres ?? 0)
                           + (float) ($l->reports_budgetaire ?? 0) + (float) ($l->reports_tresorerie ?? 0);
            $fondAlloue    = (float) ($l->dotation_etat ?? 0);
            $budgetInitial = $fondPropre + $fondAlloue;
            $modifs        = (float) ($l->transfert ?? 0);
            $budgetFinal   = $budgetInitial + $modifs;
            $engagement    = (float) ($l->engagement ?? 0);
            $solde         = $budgetFinal - $engagement;
            $taux          = $budgetFinal > 0 ? round($engagement / $budgetFinal * 100, 1) : 0;

            $etat = 'ok';
            if ($budgetFinal <= 0)          $etat = 'vide';
            elseif ($engagement > $budgetFinal) $etat = 'depasse';
            elseif ($taux >= 100)           $etat = 'epuise';
            elseif ($taux >= 90)            $etat = 'alerte';

            return (object) [
                'id'           => $l->id,
                'code'         => $l->codecompte ?: $l->id_budgetligne,
                'libelle'      => $l->budgetligne ?: ($l->commentaire ?: '—'),
                'titre'        => $l->titre?->libelle,
                'fond_propre'  => $fondPropre,
                'fond_alloue'  => $fondAlloue,
                'budget_init'  => $budgetInitial,
                'modifs'       => $modifs,
                'budget'       => $budgetFinal,
                'engagement'   => $engagement,
                'solde'        => $solde,
                'taux'         => $taux,
                'etat'         => $etat,
                // Détail (pour tooltip)
                'dotation_etat'     => (float) ($l->dotation_etat ?? 0),
                'fonds_propres'     => (float) ($l->fonds_propres ?? 0),
                'reports_budgetaire'=> (float) ($l->reports_budgetaire ?? 0),
                'reports_tresorerie'=> (float) ($l->reports_tresorerie ?? 0),
            ];
        });

        // Filtre état (post-calcul)
        if ($filtreEtat) {
            $rows = $rows->where('etat', $filtreEtat)->values();
        }

        // ── KPI globaux (sur toutes les lignes non filtrées par état)
        $totaux = [
            'fond_propre' => (float) $lignes->sum(fn($l) => (float)($l->fonds_propres ?? 0) + (float)($l->reports_budgetaire ?? 0) + (float)($l->reports_tresorerie ?? 0)),
            'fond_alloue' => (float) $lignes->sum('dotation_etat'),
            'modifs'      => (float) $lignes->sum('transfert'),
            'engagement'  => (float) $lignes->sum('engagement'),
        ];
        $totaux['budget_init'] = $totaux['fond_propre'] + $totaux['fond_alloue'];
        $totaux['budget']      = $totaux['budget_init'] + $totaux['modifs'];
        $totaux['solde']       = $totaux['budget'] - $totaux['engagement'];
        $totaux['taux']        = $totaux['budget'] > 0 ? round($totaux['engagement'] / $totaux['budget'] * 100, 1) : 0;

        // ── Sources de financement (donut : dotation / fonds propres / reports)
        $sourcesFin = [
            'dotation_etat'      => (float) $lignes->sum('dotation_etat'),
            'fonds_propres'      => (float) $lignes->sum('fonds_propres'),
            'reports_budgetaire' => (float) $lignes->sum('reports_budgetaire'),
            'reports_tresorerie' => (float) $lignes->sum('reports_tresorerie'),
        ];
        $sourcesTotal = array_sum($sourcesFin) ?: 1;

        // ── Compteurs par état (pour badges filtres)
        $compteEtat = [
            'ok'      => $lignes->reduce(fn($c, $l) => $c + $this->estEtat($l, 'ok'), 0),
            'alerte'  => $lignes->reduce(fn($c, $l) => $c + $this->estEtat($l, 'alerte'), 0),
            'epuise'  => $lignes->reduce(fn($c, $l) => $c + $this->estEtat($l, 'epuise'), 0),
            'depasse' => $lignes->reduce(fn($c, $l) => $c + $this->estEtat($l, 'depasse'), 0),
        ];

        // ── Modifs récentes appliquées sur cet exercice
        $modifsRecentes = ModificationBudgetaire::where('exercice_id', $exerciceId)
            ->where('statut', 3) // Appliquée
            ->with(['ligneSource:id,codecompte,budgetligne', 'ligneDestination:id,codecompte,budgetligne'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        // ── Titres pour le select filtre
        $titres = Titre::orderBy('imputation')->orderBy('libelle')->get(['id', 'imputation', 'libelle']);

        return view('finance.execution-budgetaire.index', compact(
            'exercicesDisponibles', 'exercice', 'exerciceId',
            'filtreTitre', 'filtreEtat', 'filtreQ',
            'rows', 'totaux', 'sourcesFin', 'sourcesTotal',
            'compteEtat', 'modifsRecentes', 'titres'
        ));
    }

    private function estEtat($ligne, string $etat): int
    {
        $planif  = (float)($ligne->dotation_etat ?? 0) + (float)($ligne->fonds_propres ?? 0) + (float)($ligne->reports_budgetaire ?? 0) + (float)($ligne->reports_tresorerie ?? 0);
        $budget  = $planif + (float)($ligne->transfert ?? 0);
        $eng     = (float)($ligne->engagement ?? 0);
        $taux    = $budget > 0 ? ($eng / $budget * 100) : 0;

        return match ($etat) {
            'depasse' => $eng > $budget ? 1 : 0,
            'epuise'  => ($budget > 0 && $eng <= $budget && $taux >= 100) ? 1 : 0,
            'alerte'  => ($budget > 0 && $taux >= 90 && $taux < 100) ? 1 : 0,
            'ok'      => ($budget > 0 && $taux < 90) ? 1 : 0,
            default   => 0,
        };
    }
}
