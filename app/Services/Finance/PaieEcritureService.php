<?php

namespace App\Services\Finance;

use App\Models\CampagnePaie;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\GrandLivre;
use App\Models\Paie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Génère les écritures comptables OHADA pour une campagne de paie validée.
 *
 * Modèle standard pour chaque campagne :
 *
 *   DÉBIT 661xxx  Salaire de base                  = Σ salaire_base
 *   DÉBIT 661200  Primes                            = Σ primes
 *   DÉBIT 661300  Indemnités                        = Σ indemnites
 *   DÉBIT 661400  Heures supplémentaires            = Σ heures_sup
 *   DÉBIT 664xxx  Charges patronales (CNSS/CNAMGS) = Σ cotisations_patronales (réparties)
 *
 *   CRÉDIT 421100 Personnel rémunérations dues      = Σ net_a_payer
 *   CRÉDIT 422200 Personnel avances déduites        = Σ avances
 *   CRÉDIT 431100 CNSS (parts sal + pat)            = (extraction des lignes)
 *   CRÉDIT 432100 CNAMGS (parts sal + pat)         = (extraction)
 *   CRÉDIT 437100 FNH                              = (extraction)
 *   CRÉDIT 437200 CFP                              = (extraction)
 *   CRÉDIT 442100 IRPP                             = Σ irpp
 *
 *  Σ DÉBITS = Σ CRÉDITS (équilibre garanti par la mécanique paie + avances)
 *
 * Idempotent : si des écritures existent déjà pour la campagne, on ne crée rien.
 */
class PaieEcritureService
{
    public function genererPourCampagne(CampagnePaie $campagne, ?int $userId = null): array
    {
        // Idempotence : pas de double génération.
        // Le lien campagne ↔ écritures se fait via ref_piece = code campagne (présent dans le fillable de GrandLivre).
        $existant = GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->count();
        if ($existant > 0) {
            return [
                'created'   => 0,
                'existant'  => $existant,
                'message'   => "Écritures déjà générées ($existant lignes).",
            ];
        }

        // Exercice cible : celui en cours, sinon l'exercice de la période de la campagne
        $exercice = Exercice::enCours()->first()
            ?? Exercice::whereYear('datedebut', '<=', $campagne->annee)
                ->whereYear('datefin', '>=', $campagne->annee)
                ->first();

        if (!$exercice) {
            throw new \RuntimeException(
                "Aucun exercice budgétaire trouvé pour la période de la campagne ({$campagne->annee}). "
                . "Créez et activez un exercice avant de valider la campagne."
            );
        }

        // Charge les bulletins validés/payés avec leurs rubriques
        $bulletins = Paie::with('rubriques')
            ->where('campagne_paie_id', $campagne->id)
            ->whereIn('statut', [1, 2])
            ->get();

        if ($bulletins->isEmpty()) {
            throw new \RuntimeException("Aucun bulletin validé dans cette campagne.");
        }

        // ─── AGRÉGATION ─────────────────────────────────────
        // Note : on utilise brut directement (et non la décomposition salaire_base/primes/indemnités/heures_sup)
        // car le brut inclut aussi les rubriques de type 'gain' (prime ancienneté, indemnité transport, panier, etc.)
        // qui ne sont pas tracées séparément. Pour la compta, un seul compte de charge suffit.
        $totalBrut        = (float) $bulletins->sum('brut');
        $totalNetAPayer   = (float) $bulletins->sum('net_a_payer');
        $totalAvances     = (float) $bulletins->sum('avances');
        $totalIrpp        = (float) $bulletins->sum('irpp');
        // bulletin.retenues = TCS + autres retenues + retenues_manuelles (hors IRPP, déjà séparé)
        $totalRetenues    = (float) $bulletins->sum('retenues');

        // Détail des cotisations par organisme (extraction des lignes de rubrique)
        $detailsCotisations = $this->extraireCotisationsParOrganisme($bulletins);

        // ─── PRÉPARATION DES LIGNES ────────────────────────
        $cfg = config('comptabilite.paie');
        $journal = config('comptabilite.journal_paie', 'PAIE');
        $numLot = sprintf('PAIE-%04d-%02d-CP%d', $campagne->annee, $campagne->mois, $campagne->id);
        $libBase = "Paie {$campagne->code}";
        $dateEcriture = now()->toDateString();

        $lignes = [];

        // — Charges (Débits) —
        // Un seul DÉBIT pour le brut total (inclut salaire base + primes + indemnités + heures sup + rubriques gain).
        // Pour les charges patronales : on prend uniquement les organismes que le PaieCalculator classe en patronal
        // (CNSS_P et CNAMGS_P via préfixe PAT_). FNH/CFP sont classées en salariale par le calculator donc
        // déjà inclus dans cotisations_salariales du bulletin → ne PAS les remettre en débit ici sinon double-comptage.
        $this->ajouterLigne($lignes, $cfg['salaire_base'],       'Salaires bruts',             $totalBrut, 'debit', $libBase);
        $this->ajouterLigne($lignes, $cfg['charges_pat_cnss'],   'Charges patronales CNSS',    $detailsCotisations['pat']['cnss'],   'debit', $libBase);
        $this->ajouterLigne($lignes, $cfg['charges_pat_cnamgs'], 'Charges patronales CNAMGS', $detailsCotisations['pat']['cnamgs'], 'debit', $libBase);

        // — Dettes (Crédits) —
        $totalCnss   = $detailsCotisations['sal']['cnss']   + $detailsCotisations['pat']['cnss'];
        $totalCnamgs = $detailsCotisations['sal']['cnamgs'] + $detailsCotisations['pat']['cnamgs'];
        $totalFnh    = $detailsCotisations['sal']['fnh']    + $detailsCotisations['pat']['fnh'];
        $totalCfp    = $detailsCotisations['sal']['cfp']    + $detailsCotisations['pat']['cfp'];

        $this->ajouterLigne($lignes, $cfg['personnel_du'],      'Net à payer au personnel',         $totalNetAPayer, 'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['personnel_avances'], 'Reprise des avances sur salaire',  $totalAvances,   'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['cnss'],              'Dette CNSS',                       $totalCnss,      'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['cnamgs'],            'Dette CNAMGS',                    $totalCnamgs,    'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['fnh'],               'Dette FNH',                       $totalFnh,       'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['cfp'],               'Dette CFP',                       $totalCfp,       'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['irpp'],              'IRPP à reverser',                  $totalIrpp,      'credit', $libBase);
        $this->ajouterLigne($lignes, $cfg['tcs'],               'TCS et autres retenues',           $totalRetenues,  'credit', $libBase);

        // Filtrer les lignes à zéro (pas de bruit comptable)
        $lignes = array_values(array_filter($lignes, fn($l) => $l['montant'] > 0));

        // Vérification d'équilibre
        $totalDebit  = array_sum(array_column(array_filter($lignes, fn($l) => $l['sens'] === 'debit'), 'montant'));
        $totalCredit = array_sum(array_column(array_filter($lignes, fn($l) => $l['sens'] === 'credit'), 'montant'));
        $delta = round($totalDebit - $totalCredit, 2);

        // ─── PERSISTANCE ────────────────────────────────────
        $created = DB::transaction(function () use ($lignes, $campagne, $exercice, $userId, $journal, $numLot, $dateEcriture) {
            $count = 0;
            $rang = 1;
            foreach ($lignes as $l) {
                $compte = Compte::where('code', $l['code'])->first();
                if (!$compte) {
                    Log::warning("Compte OHADA introuvable pour code {$l['code']}. Exécutez ComptesPaieOhadaSeeder.");
                    continue;
                }
                GrandLivre::create([
                    'date_ecriture'          => $dateEcriture,
                    'exercice'               => $exercice->exercice,
                    'id_exercicebudgetaire'  => $exercice->id,
                    'compte_id'              => $compte->id,
                    'compte_general'         => $compte->code,
                    'sens'                   => $l['sens'],
                    'montant_tc'             => $l['montant'],
                    'montant_signe_tc'       => $l['sens'] === 'debit' ? $l['montant'] : -$l['montant'],
                    'libelle'                => $l['libelle'],
                    'description'            => $l['libelle_base'],
                    'journal'                => $journal,
                    'nature'                 => 'paie',
                    'type_piece'             => 'OD',
                    'ref_piece'              => $campagne->code,
                    'num_lot'                => $numLot,
                    'num_ecriture'           => sprintf('%s-%03d', $numLot, $rang++),
                    'isvalide'               => 1,
                    'id_user'                => $userId,
                ]);
                $count++;
            }
            return $count;
        });

        return [
            'created'      => $created,
            'existant'     => 0,
            'num_lot'      => $numLot,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'delta'        => $delta,
            'equilibre'    => abs($delta) < 0.01,
            'message'      => "$created écriture(s) générée(s) sur le lot $numLot.",
        ];
    }

    /**
     * Inverse l'opération : supprime toutes les écritures liées à une campagne.
     * Utilisé lors d'une dé-validation administrative.
     */
    public function annulerPourCampagne(CampagnePaie $campagne): int
    {
        return GrandLivre::where('ref_piece', $campagne->code)->where('nature', 'paie')->delete();
    }

    private function ajouterLigne(array &$lignes, string $code, string $libelle, float $montant, string $sens, string $libBase): void
    {
        $lignes[] = [
            'code'         => $code,
            'libelle'      => "$libelle - $libBase",
            'libelle_base' => $libBase,
            'montant'      => round($montant, 2),
            'sens'         => $sens,
        ];
    }

    /**
     * Parcourt les rubriques de chaque bulletin pour distribuer les cotisations
     * par organisme. Identifie l'organisme via le libelle_court de la rubrique.
     */
    private function extraireCotisationsParOrganisme($bulletins): array
    {
        $totaux = [
            'sal' => ['cnss' => 0.0, 'cnamgs' => 0.0, 'fnh' => 0.0, 'cfp' => 0.0],
            'pat' => ['cnss' => 0.0, 'cnamgs' => 0.0, 'fnh' => 0.0, 'cfp' => 0.0],
        ];

        foreach ($bulletins as $b) {
            foreach ($b->rubriques as $r) {
                $code = $r->libelle_court; // canonique : CNSS_S, CNSS_P, CNAMGS_S, CNAMGS_P, FNH, CFP
                $montant = (float) $r->pivot->montant;
                $key = match (true) {
                    $code === 'CNSS_S'   => ['sal', 'cnss'],
                    $code === 'CNSS_P'   => ['pat', 'cnss'],
                    $code === 'CNAMGS_S' => ['sal', 'cnamgs'],
                    $code === 'CNAMGS_P' => ['pat', 'cnamgs'],
                    $code === 'FNH'      => ['pat', 'fnh'],   // FNH est patronale par convention
                    $code === 'CFP'      => ['pat', 'cfp'],   // CFP idem
                    default              => null,
                };
                if ($key) {
                    $totaux[$key[0]][$key[1]] += $montant;
                }
            }
        }
        return $totaux;
    }
}
