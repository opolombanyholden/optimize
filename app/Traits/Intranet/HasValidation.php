<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\HistoriqueValidation;
use App\Models\Intranet\Valideur;
use App\Models\User;

/**
 * Trait partagé pour le workflow de validation de clôture.
 *
 * Utilisé par : Projet, ProjetPhase, Jalon, Tache
 *
 * Pré-requis sur le modèle :
 *   - statut_cloture  (enum: ouvert, soumis, approuve, rejete, revisions)
 *   - justification_cloture (text nullable)
 *   - soumis_le, valide_le (timestamp nullable)
 *   - motif_rejet (text nullable)
 */
trait HasValidation
{
    // ── Relations ────────────────────────────────────────────

    public function valideurs()
    {
        return $this->morphMany(Valideur::class, 'validable');
    }

    public function validateursUsers()
    {
        return $this->valideurs()->where('valideur_type', 'user');
    }

    public function validateursGroupes()
    {
        return $this->valideurs()->where('valideur_type', 'groupe');
    }

    public function historiqueValidation()
    {
        return $this->morphMany(HistoriqueValidation::class, 'validable')
                     ->orderByDesc('created_at');
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Synchroniser les valideurs (users et groupes).
     */
    public function syncValideurs(array $userIds = [], array $groupeIds = []): void
    {
        $this->valideurs()->delete();

        foreach ($userIds as $uid) {
            $this->valideurs()->create([
                'valideur_type' => 'user',
                'valideur_id'   => $uid,
            ]);
        }

        foreach ($groupeIds as $gid) {
            $this->valideurs()->create([
                'valideur_type' => 'groupe',
                'valideur_id'   => $gid,
            ]);
        }
    }

    /**
     * Vérifie si l'utilisateur est valideur de cette entité.
     */
    public function estValideur(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) return false;

        // Valideur direct
        if ($this->valideurs()->where('valideur_type', 'user')->where('valideur_id', $user->id)->exists()) {
            return true;
        }

        // Membre d'un groupe valideur
        $groupeIds = $this->valideurs()->where('valideur_type', 'groupe')->pluck('valideur_id');
        if ($groupeIds->isNotEmpty()) {
            return $user->groupesIntranet()->whereIn('intranet_groupes.id', $groupeIds)->exists();
        }

        return false;
    }

    /**
     * Vérifie si des valideurs sont définis.
     */
    public function aDesValideurs(): bool
    {
        return $this->valideurs()->exists();
    }

    /**
     * Vérifie si la clôture est en attente de validation.
     */
    public function estEnAttenteValidation(): bool
    {
        return $this->statut_cloture === 'soumis';
    }

    /**
     * Vérifie si la clôture a été approuvée.
     */
    public function estClotureApprouvee(): bool
    {
        return $this->statut_cloture === 'approuve';
    }

    // ── Workflow ─────────────────────────────────────────────

    /**
     * Soumettre pour validation de clôture.
     */
    public function soumettreCloture(string $justification): void
    {
        $this->update([
            'statut_cloture'         => 'soumis',
            'justification_cloture'  => $justification,
            'soumis_le'              => now(),
            'motif_rejet'            => null,
        ]);

        $this->ajouterHistoriqueValidation('soumis', $justification);
    }

    /**
     * Approuver la clôture.
     */
    public function approuverCloture(?string $commentaire = null): void
    {
        $this->update([
            'statut_cloture' => 'approuve',
            'valide_le'      => now(),
            'motif_rejet'    => null,
        ]);

        $this->ajouterHistoriqueValidation('approuve', null, $commentaire);
    }

    /**
     * Rejeter la clôture.
     */
    public function rejeterCloture(string $motif): void
    {
        $this->update([
            'statut_cloture' => 'rejete',
            'valide_le'      => now(),
            'motif_rejet'    => $motif,
        ]);

        $this->ajouterHistoriqueValidation('rejete', null, $motif);
    }

    /**
     * Demander des révisions.
     */
    public function demanderRevisions(string $commentaire): void
    {
        $this->update([
            'statut_cloture' => 'revisions',
            'motif_rejet'    => $commentaire,
        ]);

        $this->ajouterHistoriqueValidation('revisions_demandees', null, $commentaire);
    }

    /**
     * Resoumettre après corrections.
     */
    public function resoumettreCloture(string $justification): void
    {
        $this->update([
            'statut_cloture'        => 'soumis',
            'justification_cloture' => $justification,
            'soumis_le'             => now(),
            'motif_rejet'           => null,
        ]);

        $this->ajouterHistoriqueValidation('resoumis', $justification);
    }

    /**
     * Ajouter une entrée dans l'historique de validation.
     */
    public function ajouterHistoriqueValidation(string $action, ?string $justification = null, ?string $commentaire = null, array $meta = []): HistoriqueValidation
    {
        return $this->historiqueValidation()->create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'justification' => $justification,
            'commentaire'   => $commentaire,
            'meta'          => $meta ?: null,
        ]);
    }

    // ── Accessors ────────────────────────────────────────────

    public function getClotureLibelleAttribute(): string
    {
        return match ($this->statut_cloture) {
            'ouvert'    => 'Ouvert',
            'soumis'    => 'En attente de validation',
            'approuve'  => 'Clôture approuvée',
            'rejete'    => 'Clôture rejetée',
            'revisions' => 'Révisions demandées',
            default     => 'Ouvert',
        };
    }

    public function getClotureCouleurAttribute(): string
    {
        return match ($this->statut_cloture) {
            'soumis'    => '#0891B2',
            'approuve'  => '#16A34A',
            'rejete'    => '#DC2626',
            'revisions' => '#F59E0B',
            default     => '#94A3B8',
        };
    }

    public function getClotureBadgeAttribute(): string
    {
        return match ($this->statut_cloture) {
            'soumis'    => 'warning',
            'approuve'  => 'success',
            'rejete'    => 'danger',
            'revisions' => 'warning',
            default     => 'secondary',
        };
    }
}
