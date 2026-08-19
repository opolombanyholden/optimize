<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\DemandeModification;

/**
 * Trait pour protéger les entités avec du contenu contre les modifications/suppressions.
 *
 * Une entité est "verrouillée" dès qu'elle a du contenu fille ou des interactions.
 * Seuls les super-admins peuvent modifier/supprimer directement.
 * Les autres utilisateurs doivent passer par une demande d'approbation.
 */
trait HasProtection
{
    // ── Relations ────────────────────────────────────────────

    public function demandesModification()
    {
        return $this->morphMany(DemandeModification::class, 'modifiable')
                     ->orderByDesc('created_at');
    }

    public function demandesEnAttente()
    {
        return $this->morphMany(DemandeModification::class, 'modifiable')
                     ->where('statut', 'en_attente');
    }

    // ── Vérification du verrouillage ─────────────────────────

    /**
     * Détermine si l'entité est verrouillée (a du contenu/des interactions).
     * Chaque modèle doit implémenter sa propre logique via getEstVerrouilleAttribute().
     */
    public function getEstVerrouilleAttribute(): bool
    {
        return $this->determinerVerrouillage();
    }

    /**
     * Logique de verrouillage par défaut.
     * Les modèles peuvent surcharger cette méthode.
     */
    protected function determinerVerrouillage(): bool
    {
        return false;
    }

    /**
     * Vérifie si l'utilisateur peut modifier cette entité.
     * Super-admin : toujours. Entité verrouillée : jamais (sauf super-admin).
     */
    public function peutModifier(?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) return false;

        // Super admin peut tout faire
        if ($user->hasRole('super-admin')) return true;

        // Si pas verrouillé, tout le monde peut modifier
        return ! $this->est_verrouille;
    }

    /**
     * Vérifie si l'utilisateur peut supprimer cette entité.
     */
    public function peutSupprimer(?\App\Models\User $user = null): bool
    {
        return $this->peutModifier($user);
    }

    /**
     * Vérifie si une demande de modification est en attente pour cette entité.
     */
    public function aDemandeEnAttente(): bool
    {
        return $this->demandesModification()->where('statut', 'en_attente')->exists();
    }

    // ── Actions ──────────────────────────────────────────────

    /**
     * Créer une demande de modification.
     */
    public function demanderModification(string $motif, array $champsModifies = []): DemandeModification
    {
        return $this->demandesModification()->create([
            'type_demande'    => 'modification',
            'motif'           => $motif,
            'champs_modifies' => $champsModifies ?: null,
            'demandeur_id'    => auth()->id(),
        ]);
    }

    /**
     * Créer une demande de suppression (mise en corbeille).
     */
    public function demanderSuppression(string $motif): DemandeModification
    {
        return $this->demandesModification()->create([
            'type_demande'  => 'suppression',
            'motif'         => $motif,
            'demandeur_id'  => auth()->id(),
        ]);
    }
}
