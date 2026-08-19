<?php

namespace App\Services\Finance;

use App\Models\Finance\BudgetSource;
use App\Models\Finance\Modif;
use Illuminate\Support\Facades\DB;

/**
 * Service — Application d'une modification budgétaire (Modif).
 *
 * Une Modif transfère un montant d'un BudgetSource (émission) vers un autre (réception).
 * L'application décrémente `montant` sur l'émission et l'incrémente sur la réception.
 * Réversible : `annuler` inverse l'opération.
 */
class ModifService
{
    public function verifier(Modif $modif): array
    {
        $erreurs = [];

        if ((float) $modif->montant <= 0) {
            $erreurs[] = 'Le montant doit être strictement positif.';
        }

        if (!$modif->budget_reception_id) {
            $erreurs[] = 'La cible (BudgetSource de réception) est requise.';
        }

        // Transfert : source obligatoire, différente de la réception, avec solde suffisant
        if ($modif->budget_emission_id) {
            if ($modif->budget_emission_id === $modif->budget_reception_id) {
                $erreurs[] = 'Émission et réception ne peuvent être identiques.';
            }
            $source = BudgetSource::find($modif->budget_emission_id);
            if ($source && (float) $source->montant < (float) $modif->montant) {
                $erreurs[] = sprintf(
                    'Solde insuffisant sur la source (%s disponible vs %s demandé).',
                    number_format($source->montant, 0, ',', ' '),
                    number_format((float) $modif->montant, 0, ',', ' ')
                );
            }
        }

        return $erreurs;
    }

    public function appliquer(Modif $modif, int $userId): Modif
    {
        if ($modif->status !== 2) {
            throw new \RuntimeException("Seule une modification approuvée peut être appliquée. Statut actuel : {$modif->status_libelle}.");
        }
        $erreurs = $this->verifier($modif);
        if (!empty($erreurs)) {
            throw new \RuntimeException('Modification non applicable : ' . implode(' ', $erreurs));
        }

        return DB::transaction(function () use ($modif, $userId) {
            $montant = (float) $modif->montant;

            // Décrémente la source (si transfert)
            if ($modif->budget_emission_id) {
                $emission = BudgetSource::lockForUpdate()->find($modif->budget_emission_id);
                $emission->montant = (float) $emission->montant - $montant;
                $emission->save();
            }

            // Incrémente la réception
            $reception = BudgetSource::lockForUpdate()->find($modif->budget_reception_id);
            $reception->montant = (float) $reception->montant + $montant;
            $reception->save();

            $modif->update([
                'status'       => 3,
                'applique_par' => $userId,
                'applique_at'  => now(),
            ]);
            return $modif->fresh();
        });
    }

    public function annuler(Modif $modif): Modif
    {
        if ($modif->status !== 3) {
            throw new \RuntimeException('Seule une modification appliquée peut être annulée.');
        }

        return DB::transaction(function () use ($modif) {
            $montant = (float) $modif->montant;

            if ($modif->budget_emission_id) {
                $emission = BudgetSource::lockForUpdate()->find($modif->budget_emission_id);
                $emission->montant = (float) $emission->montant + $montant;
                $emission->save();
            }
            if ($modif->budget_reception_id) {
                $reception = BudgetSource::lockForUpdate()->find($modif->budget_reception_id);
                $reception->montant = max(0, (float) $reception->montant - $montant);
                $reception->save();
            }

            $modif->update([
                'status'       => 2, // retour à approuvée
                'applique_par' => null,
                'applique_at'  => null,
            ]);
            return $modif->fresh();
        });
    }
}
