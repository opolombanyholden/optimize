<?php

namespace App\Traits\Intranet;

use App\Models\Intranet\Publication;
use App\Models\Intranet\PublicationCible;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait HasPublication
 *
 * À ajouter sur tout modèle intranet publiable.
 * Gère : visibilité (public / privé / brouillon),
 * ciblage (users / groupes / entités), options sociales.
 */
trait HasPublication
{
    // ── Relation polymorphique vers Publication ──────────────
    public function publication()
    {
        return $this->morphOne(Publication::class, 'publishable');
    }

    // ── Accès rapide aux cibles ──────────────────────────────
    public function ciblesPublication()
    {
        return $this->publication?->cibles ?? collect();
    }

    // ── Publication rapide ───────────────────────────────────
    /**
     * Publie le contenu avec la visibilité et les cibles données.
     *
     * @param string       $visibilite  'public' | 'prive' | 'brouillon'
     * @param array        $cibles      [['type'=>'user','id'=>1], ['type'=>'groupe','id'=>2], …]
     * @param array        $options     ['likes_actifs'=>true, 'commentaires_actifs'=>true, …]
     */
    public function publier(string $visibilite = 'public', array $cibles = [], array $options = []): Publication
    {
        $pub = $this->publication()->updateOrCreate(
            ['publishable_id' => $this->id, 'publishable_type' => static::class],
            array_merge([
                'visibilite'           => $visibilite,
                'likes_actifs'         => $options['likes_actifs'] ?? false,
                'commentaires_actifs'  => $options['commentaires_actifs'] ?? false,
                'partage_actif'        => $options['partage_actif'] ?? false,
                'publie_le'            => $options['publie_le'] ?? now(),
                'expire_le'            => $options['expire_le'] ?? null,
                'created_by'           => auth()->id(),
            ], $options)
        );

        // Remplacer les cibles
        PublicationCible::where('publication_id', $pub->id)->delete();
        foreach ($cibles as $cible) {
            PublicationCible::create([
                'publication_id' => $pub->id,
                'cible_type'     => $cible['type'],   // user | groupe | entite
                'cible_id'       => $cible['id'],
            ]);
        }

        return $pub;
    }

    // ── Vérifications ────────────────────────────────────────
    public function estPublic(): bool
    {
        return $this->publication?->visibilite === 'public';
    }

    public function estBrouillon(): bool
    {
        return !$this->publication || $this->publication->visibilite === 'brouillon';
    }

    public function likesActifs(): bool
    {
        return (bool) $this->publication?->likes_actifs;
    }

    public function commentairesActifs(): bool
    {
        return (bool) $this->publication?->commentaires_actifs;
    }

    /**
     * Vérifie si un utilisateur peut voir ce contenu.
     */
    public function estVisiblePar(User $user): bool
    {
        if (!$this->publication) return false;
        return $this->publication->estVisible($user);
    }

    // ── Scope : contenus visibles pour un utilisateur ────────
    public function scopeVisiblePar(Builder $query, User $user): Builder
    {
        return $query->whereHas('publication', function ($q) use ($user) {
            $q->where(function ($inner) use ($user) {
                // Publics
                $inner->where('visibilite', 'public')
                      ->orWhere(function ($priv) use ($user) {
                          // Privés + cible = user
                          $priv->where('visibilite', 'prive')
                               ->whereHas('cibles', fn($c) =>
                                   $c->where('cible_type', 'user')->where('cible_id', $user->id)
                               );
                      })
                      ->orWhere(function ($grp) use ($user) {
                          // Privés + cible = groupe dont l'user est membre
                          $groupeIds = $user->groupesIntranet()->pluck('intranet_groupes.id');
                          $grp->where('visibilite', 'prive')
                              ->whereHas('cibles', fn($c) =>
                                  $c->where('cible_type', 'groupe')->whereIn('cible_id', $groupeIds)
                              );
                      })
                      ->orWhere(function ($ent) use ($user) {
                          // Privés + cible = entité de l'user
                          $ent->where('visibilite', 'prive')
                              ->when($user->organisation_id, fn($q) =>
                                  $q->whereHas('cibles', fn($c) =>
                                      $c->where('cible_type', 'entite')->where('cible_id', $user->organisation_id)
                                  )
                              );
                      });
            });
        });
    }

    // ── Scope : publiés (pas brouillon, pas expiré) ──────────
    public function scopePublies(Builder $query): Builder
    {
        return $query->whereHas('publication', function ($q) {
            $q->whereIn('visibilite', ['public', 'prive'])
              ->where(fn($d) => $d->whereNull('publie_le')->orWhere('publie_le', '<=', now()))
              ->where(fn($e) => $e->whereNull('expire_le')->orWhere('expire_le', '>=', now()));
        });
    }
}
