<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\GrandLivre;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Exports PDF Finance (spec CdC §2 "modèle d'impression personnalisable").
 *
 *  - Budget par exercice (toutes lignes)
 *  - Grand-livre filtré (par exercice + période)
 *  - Balance des comptes (Σ débits, Σ crédits, solde par compte)
 */
class FinanceExportController extends Controller
{
    /**
     * Export PDF du budget d'un exercice (toutes les lignes).
     */
    public function budgetPdf(Exercice $exercice)
    {
        $lignes = BudgetLigne::with(['titre', 'ligne'])
            ->where('id_exercicebudgetaire', $exercice->id)
            ->orderBy('id_budgetligne')
            ->get();

        $totaux = [
            'budget'    => $lignes->sum(fn($l) => $l->budget_total),
            'engage'    => (float) $lignes->sum('engagement'),
            'transfert' => (float) $lignes->sum('transfert'),
        ];
        $totaux['disponible'] = $totaux['budget'] - $totaux['engage'];

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', 'budget-' . ($exercice->libelle ?? $exercice->exercice)) . '.pdf';

        $pdf = Pdf::loadView('finance.exports.budget-pdf', compact('exercice', 'lignes', 'totaux'))
            ->setPaper('A4', 'landscape')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);
        return $pdf->stream($filename);
    }

    /**
     * Export PDF du grand-livre filtré.
     */
    public function grandLivrePdf(Request $request)
    {
        $params = $request->validate([
            'exercice_id' => 'nullable|exists:exercices,id',
            'compte_id'   => 'nullable|exists:comptes,id',
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date|after_or_equal:date_debut',
            'ref_piece'   => 'nullable|string|max:100',
        ]);

        $ecritures = GrandLivre::with(['compte', 'user'])
            ->when($params['exercice_id'] ?? null, fn($q, $id) => $q->where('id_exercicebudgetaire', $id))
            ->when($params['compte_id'] ?? null, fn($q, $id) => $q->where('compte_id', $id))
            ->when($params['date_debut'] ?? null, fn($q, $d) => $q->where('date_ecriture', '>=', $d))
            ->when($params['date_fin'] ?? null, fn($q, $d) => $q->where('date_ecriture', '<=', $d))
            ->when($params['ref_piece'] ?? null, fn($q, $r) => $q->where('ref_piece', $r))
            ->orderBy('date_ecriture')
            ->orderBy('id')
            ->get();

        $exercice = !empty($params['exercice_id']) ? Exercice::find($params['exercice_id']) : null;
        $compte   = !empty($params['compte_id']) ? Compte::find($params['compte_id']) : null;

        $totaux = [
            'debit'  => (float) $ecritures->where('sens', 'debit')->sum('montant_tc'),
            'credit' => (float) $ecritures->where('sens', 'credit')->sum('montant_tc'),
        ];
        $totaux['solde'] = $totaux['debit'] - $totaux['credit'];

        $filename = 'grand-livre-' . now()->format('Ymd') . '.pdf';

        $pdf = Pdf::loadView('finance.exports.grand-livre-pdf', compact('ecritures', 'exercice', 'compte', 'totaux', 'params'))
            ->setPaper('A4', 'landscape')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);
        return $pdf->stream($filename);
    }

    /**
     * Export PDF de la balance des comptes pour un exercice.
     */
    public function balancePdf(Exercice $exercice)
    {
        // Pour chaque compte ayant des écritures sur l'exercice : Σ débits, Σ crédits, solde
        $rows = GrandLivre::query()
            ->selectRaw('compte_id, compte_general,
                COALESCE(SUM(CASE WHEN sens = ? THEN montant_tc ELSE 0 END), 0) as total_debit,
                COALESCE(SUM(CASE WHEN sens = ? THEN montant_tc ELSE 0 END), 0) as total_credit,
                COUNT(*) as nb_ecritures', ['debit', 'credit'])
            ->where('id_exercicebudgetaire', $exercice->id)
            ->groupBy('compte_id', 'compte_general')
            ->orderBy('compte_general')
            ->get();

        $comptes = Compte::whereIn('id', $rows->pluck('compte_id'))->get()->keyBy('id');

        $balance = $rows->map(function ($r) use ($comptes) {
            $solde = $r->total_debit - $r->total_credit;
            return [
                'code'        => $r->compte_general,
                'libelle'     => $comptes[$r->compte_id]?->nom ?? '—',
                'nb'          => $r->nb_ecritures,
                'debit'       => (float) $r->total_debit,
                'credit'      => (float) $r->total_credit,
                'solde'       => $solde,
                'sens_solde'  => $solde >= 0 ? 'D' : 'C',
            ];
        });

        $totaux = [
            'debit'  => $balance->sum('debit'),
            'credit' => $balance->sum('credit'),
        ];

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', 'balance-' . ($exercice->libelle ?? $exercice->exercice)) . '.pdf';

        $pdf = Pdf::loadView('finance.exports.balance-pdf', compact('exercice', 'balance', 'totaux'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans']);
        return $pdf->stream($filename);
    }
}
