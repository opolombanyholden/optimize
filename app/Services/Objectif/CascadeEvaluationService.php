<?php

namespace App\Services\Objectif;

use App\Models\Employee;
use App\Models\Intranet\Evaluation;
use App\Models\Intranet\Objectif;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Cascade automatique : lorsqu'un objectif individuel atteint son statut final
 * (atteint / non_atteint), une évaluation est créée ou mise à jour pour servir
 * de base à la revue du collaborateur par son N+1.
 *
 * Règles :
 *   - Ne traite QUE les objectifs de portée 'individuel' avec un responsable_id défini
 *   - L'évaluateur est déterminé via Employee::superieur_hierarchique du responsable
 *   - Si aucun N+1 trouvé : on skip et on log (l'admin RH devra créer manuellement)
 *   - Si une évaluation existe déjà pour ce couple (objectif, user), on la met à jour en brouillon
 */
class CascadeEvaluationService
{
    /**
     * Déclenche la cascade pour un objectif qui vient d'atteindre un statut final.
     * Retourne l'Evaluation créée/MAJ, ou null si rien à faire.
     */
    public function cascader(Objectif $objectif): ?Evaluation
    {
        // Ne traiter que les objectifs individuels avec responsable
        if ($objectif->portee !== 'individuel' || !$objectif->responsable_id) {
            return null;
        }
        // Ne traiter que les statuts finaux
        if (!in_array($objectif->statut, ['atteint', 'non_atteint'], true)) {
            return null;
        }

        $user = User::find($objectif->responsable_id);
        if (!$user) {
            Log::info('Cascade évaluation : responsable user introuvable', ['objectif_id' => $objectif->id]);
            return null;
        }

        $evaluateur = $this->resoudreNPlusUn($user);
        if (!$evaluateur) {
            Log::warning('Cascade évaluation : N+1 non trouvé pour le responsable', [
                'objectif_id' => $objectif->id,
                'user_id'     => $user->id,
            ]);
            // On crée quand même un brouillon, mais sans évaluateur — l'admin RH complètera.
            // Pour rester compatible avec la contrainte NOT NULL, on prend le créateur de l'objectif
            // comme fallback (ou super-admin).
            $evaluateur = $objectif->auteur ?? User::role('super-admin')->first();
            if (!$evaluateur) return null;
        }

        // Score suggéré = progression de l'objectif
        $scoreSuggere = $this->scoreSuggere($objectif);

        $titre = sprintf('Évaluation : %s', $objectif->titre);
        $commentaire = $this->commentairePrerempli($objectif);

        // Upsert : si une évaluation existe déjà pour (user, objectif) → MAJ
        $eval = Evaluation::firstOrNew([
            'user_id'     => $user->id,
            'objectif_id' => $objectif->id,
        ]);

        $eval->fill([
            'titre'             => $eval->titre ?: $titre,
            'evaluateur_id'     => $eval->evaluateur_id ?: $evaluateur->id,
            'score'             => $eval->score ?: $scoreSuggere,
            'commentaire'       => $eval->commentaire ?: $commentaire,
            'date_evaluation'   => $eval->date_evaluation ?: now()->toDateString(),
            'statut'            => $eval->statut === 'valide' ? 'valide' : 'brouillon',
        ])->save();

        Log::info('Cascade évaluation créée/MAJ', [
            'objectif_id'  => $objectif->id,
            'evaluation_id' => $eval->id,
            'user_id'      => $user->id,
            'evaluateur_id' => $evaluateur->id,
        ]);

        return $eval;
    }

    /**
     * Retourne le User correspondant au supérieur hiérarchique du responsable.
     */
    private function resoudreNPlusUn(User $user): ?User
    {
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee || !$employee->superieur_hierarchique) {
            return null;
        }
        $superieur = Employee::find($employee->superieur_hierarchique);
        return $superieur?->user;
    }

    private function scoreSuggere(Objectif $objectif): int
    {
        return $objectif->statut === 'atteint'
            ? max(70, $objectif->progression)   // au moins 70 si atteint
            : min(50, $objectif->progression);  // plafonné à 50 si non atteint
    }

    private function commentairePrerempli(Objectif $objectif): string
    {
        $statutLib = $objectif->statut === 'atteint' ? 'atteint' : 'non atteint';
        return sprintf(
            "Objectif individuel « %s » %s avec une progression de %d%%.\n\nÉchéance : %s\nÀ compléter par l'évaluateur lors de l'entretien.",
            $objectif->titre,
            $statutLib,
            $objectif->progression,
            $objectif->date_fin?->format('d/m/Y') ?? '—'
        );
    }
}
