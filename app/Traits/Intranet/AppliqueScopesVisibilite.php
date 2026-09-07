<?php

namespace App\Traits\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Filtre de scope à 3 axes : mes / groupes / publiques.
 * Utilisé par les contrôleurs de listes du module Gestion de Projet.
 *
 * Usage :
 *   $q = Tache::query();
 *   $this->appliqueScopesVisibilite($q, $request, [
 *       'mes_columns'       => ['responsable_id', 'created_by'],
 *       'mes_relations'     => ['assignes'],
 *   ]);
 */
trait AppliqueScopesVisibilite
{
    protected function appliqueScopesVisibilite(Builder $query, Request $request, array $config = []): Builder
    {
        $scopes = (array) $request->input('scopes', []);
        if (empty($scopes)) return $query; // aucun filtre → comportement par défaut (tout visible)

        $user = $request->user();
        if (!$user) return $query;

        $mesColumns   = $config['mes_columns']   ?? ['created_by'];
        $mesRelations = $config['mes_relations'] ?? [];

        return $query->where(function (Builder $q) use ($scopes, $user, $mesColumns, $mesRelations) {

            // ── SCOPE "mes" : créateur, responsable, assigné, chef, sponsor... ──
            if (in_array('mes', $scopes, true)) {
                $q->orWhere(function (Builder $sub) use ($user, $mesColumns, $mesRelations) {
                    foreach ($mesColumns as $col) {
                        $sub->orWhere($col, $user->id);
                    }
                    foreach ($mesRelations as $rel) {
                        $sub->orWhereHas($rel, fn($x) => $x->where('users.id', $user->id));
                    }
                });
            }

            // ── SCOPE "groupes" : publication privée ciblée sur un groupe dont l'user est membre ──
            if (in_array('groupes', $scopes, true)) {
                $groupeIds = $user->groupesIntranet()->pluck('intranet_groupes.id');
                if ($groupeIds->isNotEmpty()) {
                    $q->orWhereHas('publication', function ($p) use ($groupeIds) {
                        $p->where('visibilite', 'prive')
                          ->whereHas('cibles', fn($c) =>
                              $c->where('cible_type', 'groupe')->whereIn('cible_id', $groupeIds)
                          );
                    });
                }
            }

            // ── SCOPE "publiques" : publication marquée public ──
            if (in_array('publiques', $scopes, true)) {
                $q->orWhereHas('publication', fn($p) => $p->where('visibilite', 'public'));
            }
        });
    }
}
