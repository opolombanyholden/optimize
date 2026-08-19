<?php

namespace App\Services\Finance;

use App\Models\BudgetLigne;
use App\Models\ModificationBudgetaire;
use Illuminate\Support\Facades\DB;

/**
 * Applique une modification budgétaire approuvée sur les lignes concernées.
 *
 * Modes :
 *   - 'transfert' : retire le montant de la ligne SOURCE, l'ajoute sur la ligne DESTINATION.
 *                   Le total budget de l'exercice reste invariant.
 *   - 'ajout'     : ajoute le montant à la ligne DESTINATION (sans source).
 *                   Le total budget de l'exercice augmente.
 *
 * Stocké via la colonne `transfert` de BudgetLigne (delta cumulé des transferts entrants/sortants).
 * Si la ligne n'a pas de transfert encore : on initialise.
 */
class ModificationBudgetaireService
{
    /**
     * Vérifie qu'on peut appliquer (lignes existent, source a assez de budget, etc.).
     */
    public function verifier(ModificationBudgetaire $mod): array
    {
        $erreurs = [];

        if (!in_array($mod->type_modification, ['transfert', 'ajout'])) {
            $erreurs[] = "Type inconnu : {$mod->type_modification}";
        }

        if ((float) $mod->montant_modification <= 0) {
            $erreurs[] = "Le montant doit être strictement positif.";
        }

        if ($mod->type_modification === 'transfert') {
            if (!$mod->budget_ligne_source_id) {
                $erreurs[] = "Ligne source requise pour un transfert.";
            }
            if (!$mod->budget_ligne_destination_id) {
                $erreurs[] = "Ligne destination requise pour un transfert.";
            }
            if ($mod->budget_ligne_source_id === $mod->budget_ligne_destination_id) {
                $erreurs[] = "Source et destination ne peuvent être identiques.";
            }
            if ($mod->budget_ligne_source_id) {
                $source = BudgetLigne::find($mod->budget_ligne_source_id);
                if ($source && $source->solde_disponible < (float) $mod->montant_modification) {
                    $erreurs[] = sprintf(
                        "Solde insuffisant sur la ligne source : %s disponible vs %s demandé.",
                        number_format($source->solde_disponible, 0, ',', ' '),
                        number_format((float) $mod->montant_modification, 0, ',', ' ')
                    );
                }
            }
        } else { // ajout
            if (!$mod->budget_ligne_destination_id) {
                $erreurs[] = "Ligne destination requise pour un apport.";
            }
        }

        return $erreurs;
    }

    /**
     * Applique la modification. Doit être en statut « approuvée ».
     * Modifie le champ `transfert` des lignes : +N pour destination, -N pour source.
     */
    public function appliquer(ModificationBudgetaire $mod, int $userId): ModificationBudgetaire
    {
        if ($mod->statut !== 2) {
            throw new \RuntimeException("Seule une modification approuvée peut être appliquée. Statut actuel : {$mod->statut_libelle}.");
        }

        $erreurs = $this->verifier($mod);
        if (!empty($erreurs)) {
            throw new \RuntimeException("Modification non applicable : " . implode(' ', $erreurs));
        }

        return DB::transaction(function () use ($mod, $userId) {
            $montant = (float) $mod->montant_modification;

            if ($mod->type_modification === 'transfert') {
                $source = BudgetLigne::lockForUpdate()->find($mod->budget_ligne_source_id);
                $source->transfert = ((float) ($source->transfert ?? 0)) - $montant;
                $source->save();
            }

            $destination = BudgetLigne::lockForUpdate()->find($mod->budget_ligne_destination_id);
            $destination->transfert = ((float) ($destination->transfert ?? 0)) + $montant;
            $destination->save();

            $mod->update([
                'statut'       => 3, // appliquée
                'applique_par' => $userId,
                'applique_at'  => now(),
            ]);

            return $mod->fresh();
        });
    }

    /**
     * Annule une modification appliquée (réinverse les transferts).
     * Repasse en statut « approuvée » pour permettre une ré-application après correction.
     */
    public function annuler(ModificationBudgetaire $mod): ModificationBudgetaire
    {
        if ($mod->statut !== 3) {
            throw new \RuntimeException("Seule une modification appliquée peut être annulée.");
        }

        return DB::transaction(function () use ($mod) {
            $montant = (float) $mod->montant_modification;

            if ($mod->type_modification === 'transfert' && $mod->budget_ligne_source_id) {
                $source = BudgetLigne::lockForUpdate()->find($mod->budget_ligne_source_id);
                $source->transfert = ((float) ($source->transfert ?? 0)) + $montant; // re-crédite
                $source->save();
            }

            if ($mod->budget_ligne_destination_id) {
                $destination = BudgetLigne::lockForUpdate()->find($mod->budget_ligne_destination_id);
                $destination->transfert = ((float) ($destination->transfert ?? 0)) - $montant; // re-débite
                $destination->save();
            }

            $mod->update([
                'statut'       => 2, // retour à approuvée
                'applique_par' => null,
                'applique_at'  => null,
            ]);

            return $mod->fresh();
        });
    }
}
