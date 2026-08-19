<?php

namespace App\Services\Finance;

use App\Models\BudgetLigne;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\GrandLivre;
use App\Models\OperationFinanciere;
use App\Models\OperationFinanciereDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service métier pour les opérations financières (ordres de dépense / recette).
 *
 * Effets de l'exécution :
 *  - Pour une DÉPENSE :
 *      • Engagement sur la ligne budgétaire (budget_ligne.engagement += montant)
 *      • Si facture rattachée : montant_regle += montant ; statut facture évolue
 *      • Génération de 2 écritures comptables :
 *          DÉBIT  compte de charge (621100 service ou compte de la ligne si dispo)
 *          CRÉDIT compte trésorerie (521100 banque) ou fournisseur (401)
 *  - Pour une RECETTE : opération miroir (recette → encaissement)
 */
class OperationFinanciereService
{
    /**
     * Vérifie la cohérence avant exécution.
     */
    public function verifier(OperationFinanciere $op): array
    {
        $erreurs = [];

        if ($op->type_operation === 'depense') {
            if (!$op->budget_ligne_id) {
                $erreurs[] = 'Une dépense doit être affectée à une ligne budgétaire.';
            } else {
                $ligne = BudgetLigne::find($op->budget_ligne_id);
                if (!$ligne) {
                    $erreurs[] = 'Ligne budgétaire introuvable.';
                } elseif ($ligne->isvalide != 1) {
                    $erreurs[] = "La ligne budgétaire doit être validée avant tout engagement.";
                } elseif ($ligne->solde_disponible < (float) $op->montant) {
                    $erreurs[] = sprintf(
                        "Solde insuffisant sur la ligne : %s disponible vs %s demandé.",
                        number_format($ligne->solde_disponible, 0, ',', ' '),
                        number_format((float) $op->montant, 0, ',', ' ')
                    );
                }
            }
        }

        if ((float) $op->montant <= 0) {
            $erreurs[] = "Le montant doit être strictement positif.";
        }

        if (!$op->date_operation) {
            $erreurs[] = "Date d'opération requise.";
        }

        return $erreurs;
    }

    /**
     * Exécute l'opération : engagement budget + écritures comptables.
     */
    public function executer(OperationFinanciere $op, int $userId): OperationFinanciere
    {
        if ($op->statut !== 2) {
            throw new \RuntimeException("Seule une opération approuvée peut être exécutée. Statut : {$op->statut_libelle}.");
        }

        $erreurs = $this->verifier($op);
        if (!empty($erreurs)) {
            throw new \RuntimeException("Opération non exécutable : " . implode(' ', $erreurs));
        }

        return DB::transaction(function () use ($op, $userId) {
            // 1. Engagement sur la ligne budgétaire (dépense seulement)
            if ($op->type_operation === 'depense' && $op->budget_ligne_id) {
                $ligne = BudgetLigne::lockForUpdate()->find($op->budget_ligne_id);
                $ligne->engagement = ((float) ($ligne->engagement ?? 0)) + (float) $op->montant;
                $ligne->save();
            }

            // 2. Marquer la facture comme partiellement/totalement réglée
            if ($op->facture_id) {
                $facture = Facture::lockForUpdate()->find($op->facture_id);
                if ($facture) {
                    $facture->montant_regle = ((float) ($facture->montant_regle ?? 0)) + (float) $op->montant;
                    $facture->statut = $facture->montant_regle >= (float) $facture->montant_ttc ? 3 : 2;
                    $facture->save();
                }
            }

            // 3. Écritures comptables (best-effort : ne plante pas si pas d'exercice)
            $this->genererEcritures($op);

            // 4. Marquer l'opération comme exécutée
            $op->update([
                'statut'      => 3,
                'execute_par' => $userId,
                'execute_at'  => now(),
            ]);

            return $op->fresh();
        });
    }

    /**
     * Annule l'exécution : libère l'engagement budget, défait les écritures.
     */
    public function annuler(OperationFinanciere $op): OperationFinanciere
    {
        if ($op->statut !== 3) {
            throw new \RuntimeException("Seule une opération exécutée peut être annulée.");
        }

        return DB::transaction(function () use ($op) {
            // 1. Libère l'engagement
            if ($op->type_operation === 'depense' && $op->budget_ligne_id) {
                $ligne = BudgetLigne::lockForUpdate()->find($op->budget_ligne_id);
                $ligne->engagement = max(0, ((float) ($ligne->engagement ?? 0)) - (float) $op->montant);
                $ligne->save();
            }
            // 2. Détache la facture
            if ($op->facture_id) {
                $facture = Facture::lockForUpdate()->find($op->facture_id);
                if ($facture) {
                    $facture->montant_regle = max(0, ((float) $facture->montant_regle) - (float) $op->montant);
                    $facture->statut = $facture->montant_regle > 0 ? 2 : 1;
                    $facture->save();
                }
            }
            // 3. Supprime les écritures rattachées
            GrandLivre::where('ref_piece', $op->numero)
                ->where('nature', 'operation')
                ->delete();

            $op->update([
                'statut'      => 5, // annulée
                'execute_par' => null,
                'execute_at'  => null,
            ]);
            return $op->fresh();
        });
    }

    /**
     * Génère les écritures comptables pour une opération.
     * Best-effort : si pas d'exercice en cours ou pas de compte trouvé, on log et on continue.
     */
    private function genererEcritures(OperationFinanciere $op): int
    {
        $exercice = Exercice::find($op->exercice_id) ?? Exercice::enCours()->first();
        if (!$exercice) return 0;

        $journal = 'OD';
        $libelle = "Op. {$op->numero} — {$op->objet}";

        // Comptes selon type d'opération
        if ($op->type_operation === 'depense') {
            // DÉBIT charge / CRÉDIT trésorerie
            $compteCharge    = Compte::where('code', '621100')->first() ?? Compte::where('code', '601100')->first();
            $compteTreso     = Compte::where('code', '521100')->first(); // banque
            $compteFournisseur = Compte::where('code', '401100')->first();

            if (!$compteTreso) return 0;

            // Si on a un fournisseur, on passe par 401 ; sinon directement charge / trésorerie
            if ($op->tiers_type === 'fournisseur' && $compteFournisseur) {
                $this->creerLigne($op, $exercice, $journal, $compteCharge,      'debit',  (float) $op->montant, "$libelle (charge)");
                $this->creerLigne($op, $exercice, $journal, $compteFournisseur, 'credit', (float) $op->montant, "$libelle (fournisseur)");
            } else {
                $this->creerLigne($op, $exercice, $journal, $compteCharge, 'debit',  (float) $op->montant, "$libelle (charge)");
                $this->creerLigne($op, $exercice, $journal, $compteTreso,  'credit', (float) $op->montant, "$libelle (paiement)");
            }
        } else {
            // Recette : DÉBIT trésorerie / CRÉDIT produit
            $compteProduit = Compte::where('code', '701100')->first() ?? Compte::where('code', '706100')->first();
            $compteTreso   = Compte::where('code', '521100')->first();
            $compteClient  = Compte::where('code', '411100')->first();

            if (!$compteTreso) return 0;

            if ($op->tiers_type === 'client' && $compteClient) {
                $this->creerLigne($op, $exercice, $journal, $compteClient,  'debit',  (float) $op->montant, "$libelle (client)");
                $this->creerLigne($op, $exercice, $journal, $compteProduit, 'credit', (float) $op->montant, "$libelle (produit)");
            } else {
                $this->creerLigne($op, $exercice, $journal, $compteTreso,   'debit',  (float) $op->montant, "$libelle (encaissement)");
                $this->creerLigne($op, $exercice, $journal, $compteProduit, 'credit', (float) $op->montant, "$libelle (produit)");
            }
        }

        return 2;
    }

    private function creerLigne(
        OperationFinanciere $op,
        Exercice $exercice,
        string $journal,
        ?Compte $compte,
        string $sens,
        float $montant,
        string $libelle
    ): void {
        if (!$compte || $montant <= 0) return;
        GrandLivre::create([
            'date_ecriture'         => $op->date_operation,
            'exercice'              => $exercice->exercice,
            'id_exercicebudgetaire' => $exercice->id,
            'compte_id'             => $compte->id,
            'compte_general'        => $compte->code,
            'sens'                  => $sens,
            'montant_tc'            => $montant,
            'montant_signe_tc'      => $sens === 'debit' ? $montant : -$montant,
            'libelle'               => $libelle,
            'description'           => $op->objet,
            'journal'               => $journal,
            'nature'                => 'operation',
            'type_piece'            => 'OD',
            'ref_piece'             => $op->numero,
            'num_lot'               => "OP-{$op->id}",
            'num_ecriture'          => $op->numero . '-' . substr(Str::uuid(), 0, 6),
            'isvalide'              => 1,
            'id_user'               => $op->created_by,
        ]);
    }

    /**
     * Synchronise les détails d'une opération (lignes de rubriques).
     * Recalcule le montant total de l'opération depuis les détails.
     *
     * @param array $lignes [[rubrique_id, libelle, quantite, prix_unitaire, observation], ...]
     */
    public function syncDetails(OperationFinanciere $op, array $lignes): OperationFinanciere
    {
        if (!$op->est_modifiable) {
            throw new \RuntimeException("Détails non modifiables (statut : {$op->statut_libelle}).");
        }

        return DB::transaction(function () use ($op, $lignes) {
            // Purge des anciens détails
            $op->details()->delete();

            $total = 0;
            $rang = 1;
            foreach ($lignes as $l) {
                if (empty($l['libelle'])) continue;
                $quantite = (float) ($l['quantite'] ?? 1);
                $pu       = (float) ($l['prix_unitaire'] ?? 0);
                $montant  = $quantite > 0 && $pu > 0
                    ? round($quantite * $pu, 2)
                    : (float) ($l['montant'] ?? 0);
                if ($montant <= 0) continue;

                OperationFinanciereDetail::create([
                    'operation_financiere_id' => $op->id,
                    'rubrique_id'             => !empty($l['rubrique_id']) ? (int) $l['rubrique_id'] : null,
                    'libelle'                 => substr($l['libelle'], 0, 500),
                    'quantite'                => $quantite,
                    'prix_unitaire'           => $pu,
                    'montant'                 => $montant,
                    'ordre'                   => $rang++,
                    'observation'             => $l['observation'] ?? null,
                ]);
                $total += $montant;
            }

            // Si des détails existent, le montant total devient leur somme.
            // Sinon on conserve le montant saisi à la main.
            if ($total > 0) {
                $op->update(['montant' => round($total, 2)]);
            }

            return $op->fresh(['details.rubrique']);
        });
    }

    /**
     * Génère un numéro unique format OP-YYYY-MM-NNN.
     */
    public static function genererNumero(string $type): string
    {
        $prefix = $type === 'depense' ? 'OD' : 'OR';
        $base = sprintf('%s-%s', $prefix, now()->format('Y-m'));
        $dernier = OperationFinanciere::where('numero', 'like', "$base-%")
            ->orderByDesc('numero')->first();
        if ($dernier && preg_match("/$base-(\d+)/", $dernier->numero, $m)) {
            $n = (int) $m[1] + 1;
        } else {
            $n = 1;
        }
        return sprintf('%s-%03d', $base, $n);
    }

    public static function genererNumeroFacture(string $sens): string
    {
        $prefix = $sens === 'depense' ? 'FF' : 'FC'; // Facture Fournisseur / Facture Client
        $base = sprintf('%s-%s', $prefix, now()->format('Y-m'));
        $dernier = Facture::where('numero', 'like', "$base-%")
            ->orderByDesc('numero')->first();
        if ($dernier && preg_match("/$base-(\d+)/", $dernier->numero, $m)) {
            $n = (int) $m[1] + 1;
        } else {
            $n = 1;
        }
        return sprintf('%s-%03d', $base, $n);
    }
}
