<?php

namespace App\Services\Social;

use App\Models\Absence;
use App\Models\CommandeFournisseur;
use App\Models\CommandeInterne;
use App\Models\DevisFournisseur;
use App\Models\Dysfonctionnement;
use App\Models\Employee;
use App\Models\Facture;
use App\Models\Formation;
use App\Models\Immobilisation;
use App\Models\Intervention;
use App\Models\Intranet\Annonce;
use App\Models\Intranet\Courrier;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\Media;
use App\Models\Intranet\News;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\Projet;
use App\Models\Intranet\Ressource;
use App\Models\Intranet\Tache;
use App\Models\LivraisonFournisseur;
use App\Models\Social\Post;
use App\Models\User;

/**
 * Résout et normalise n'importe quelle référence "partageable" en chat.
 *
 * Un contenu partageable = est PUBLIC (via HasPublication.visibilite === 'public'
 * ou is_public === true selon le modèle) OU appartient à l'utilisateur / dont
 * l'utilisateur fait partie (équipe projet, assigné tâche, etc.).
 *
 * ⚠️ Les référentiels et les données d'administration ne sont PAS partageables.
 */
class ShareableResolver
{
    public const TYPES = [
        // Réseau social
        'post'       => Post::class,
        // Intranet
        'news'       => News::class,
        'annonce'    => Annonce::class,
        'courrier'   => Courrier::class,
        'ressource'  => Ressource::class,
        'media'      => Media::class,
        'evenement'  => Evenement::class,
        // Finance
        'facture'    => Facture::class,
        // GRH & Paie
        'employee'   => Employee::class,
        'absence'    => Absence::class,
        'formation'  => Formation::class,
        // Achats & Moyens Généraux
        'commande'   => CommandeFournisseur::class,
        'demande'    => CommandeInterne::class,
        'devis'      => DevisFournisseur::class,
        'livraison'  => LivraisonFournisseur::class,
        'dysfonc'    => Dysfonctionnement::class,
        'intervention'=> Intervention::class,
        'immo'       => Immobilisation::class,
        // Projets
        'projet'     => Projet::class,
        'tache'      => Tache::class,
        // Stratégie
        'objectif'   => Objectif::class,
        'kpi'        => Kpi::class,
    ];

    public const META = [
        Post::class             => ['label' => 'Publication',       'icon' => 'fa-square-share-nodes', 'title' => 'contenu',        'excerpt' => null,           'route' => 'social.posts.show'],
        News::class             => ['label' => 'Actualité',         'icon' => 'fa-newspaper',          'title' => 'title',          'excerpt' => 'extrait',      'route' => 'intranet.news.show'],
        Annonce::class          => ['label' => 'Annonce',           'icon' => 'fa-bullhorn',           'title' => 'title',          'excerpt' => 'extrait',      'route' => 'intranet.annonces.show'],
        Courrier::class         => ['label' => 'Courrier',          'icon' => 'fa-envelope',           'title' => 'objet',          'excerpt' => 'extrait',      'route' => 'intranet.courriers.show'],
        Ressource::class        => ['label' => 'Document',          'icon' => 'fa-file-lines',         'title' => 'titre',          'excerpt' => 'description',  'route' => 'intranet.ressources.show'],
        Media::class            => ['label' => 'Média',             'icon' => 'fa-photo-film',         'title' => 'titre',          'excerpt' => 'description',  'route' => 'intranet.mediatheque.show'],
        Evenement::class        => ['label' => 'Événement',         'icon' => 'fa-calendar-day',       'title' => 'titre',          'excerpt' => 'extrait',      'route' => 'intranet.evenements.show'],
        Facture::class          => ['label' => 'Facture',           'icon' => 'fa-file-invoice-dollar','title' => 'numero',         'excerpt' => 'objet',        'route' => 'finance.factures.show'],
        Employee::class         => ['label' => 'Collaborateur',     'icon' => 'fa-id-badge',           'title' => 'noms',           'excerpt' => 'poste',        'route' => 'rh.employees.show'],
        Absence::class          => ['label' => 'Absence',           'icon' => 'fa-user-clock',         'title' => 'label',          'excerpt' => null,           'route' => 'rh.absences.show'],
        Formation::class        => ['label' => 'Formation',         'icon' => 'fa-graduation-cap',     'title' => 'label',          'excerpt' => null,           'route' => 'rh.formations.show'],
        CommandeFournisseur::class => ['label' => 'Commande fournisseur', 'icon' => 'fa-cart-flatbed',   'title' => 'reference',   'excerpt' => 'objet',        'route' => 'appro.commandes.show'],
        CommandeInterne::class     => ['label' => 'Demande interne',      'icon' => 'fa-clipboard-list', 'title' => 'reference',   'excerpt' => null,           'route' => 'appro.commandes-internes.show'],
        DevisFournisseur::class    => ['label' => 'Devis fournisseur',    'icon' => 'fa-file-signature','title' => 'reference',    'excerpt' => null,           'route' => 'appro.devis-fournisseur.show'],
        LivraisonFournisseur::class=> ['label' => 'Livraison fournisseur','icon' => 'fa-truck',         'title' => 'reference',    'excerpt' => null,           'route' => 'appro.livraisons-fournisseur.show'],
        Dysfonctionnement::class   => ['label' => 'Ticket',               'icon' => 'fa-triangle-exclamation','title'=> 'label',   'excerpt' => 'description',  'route' => 'mg.dysfonctionnements.show'],
        Intervention::class        => ['label' => 'Intervention',         'icon' => 'fa-wrench',        'title' => 'label',        'excerpt' => 'description',  'route' => 'mg.interventions.show'],
        Immobilisation::class      => ['label' => 'Immobilisation',       'icon' => 'fa-warehouse',     'title' => 'designation',  'excerpt' => 'description',  'route' => 'mg.immobilisations.show'],
        Projet::class              => ['label' => 'Projet',               'icon' => 'fa-diagram-project','title'=> 'nom',          'excerpt' => 'description',  'route' => 'intranet.projets.show'],
        Tache::class               => ['label' => 'Tâche',                'icon' => 'fa-list-check',    'title' => 'titre',        'excerpt' => 'resume',       'route' => 'intranet.taches.show'],
        Objectif::class            => ['label' => 'Objectif',             'icon' => 'fa-bullseye',      'title' => 'titre',        'excerpt' => 'description',  'route' => 'objectifs.objectifs.show'],
        Kpi::class                 => ['label' => 'KPI',                  'icon' => 'fa-chart-line',    'title' => 'titre',        'excerpt' => 'description',  'route' => 'objectifs.kpi.show'],
    ];

    /**
     * 6 modules métier. Référentiels + admin volontairement exclus.
     */
    /**
     * Correspondance (module, section) → type court (pour la syntaxe inline @_mod&@_sec&@_titre).
     */
    public const SECTIONS = [
        'intranet' => [
            'posts'       => 'post',       // alias social
            'news'        => 'news',
            'actualites'  => 'news',
            'annonces'    => 'annonce',
            'courriers'   => 'courrier',
            'ressources'  => 'ressource',
            'documents'   => 'ressource',
            'mediatheque' => 'media',
            'medias'      => 'media',
            'evenements'  => 'evenement',
            'agenda'      => 'evenement',
        ],
        'social' => ['posts' => 'post'],
        'finance' => ['factures' => 'facture'],
        'appro' => [
            'commandes' => 'commande',
            'demandes'  => 'demande',
            'devis'     => 'devis',
            'livraisons'=> 'livraison',
        ],
        'mg' => [
            'tickets'         => 'dysfonc',
            'dysfonctionnements' => 'dysfonc',
            'interventions'   => 'intervention',
            'immobilisations' => 'immo',
        ],
        'rh' => [
            'employes'   => 'employee',
            'employees'  => 'employee',
            'absences'   => 'absence',
            'formations' => 'formation',
        ],
        'projet' => [
            'projets' => 'projet',
            'taches'  => 'tache',
        ],
        'strategie' => [
            'objectifs' => 'objectif',
            'kpis'      => 'kpi',
            'kpi'       => 'kpi',
        ],
    ];

    public const MODULES = [
        'all'      => ['label' => 'Tous les modules',        'types' => ['post','news','annonce','courrier','ressource','media','evenement','facture','employee','absence','formation','commande','demande','devis','livraison','dysfonc','intervention','immo','projet','tache','objectif','kpi']],
        'intranet' => ['label' => 'Intranet',                'types' => ['post','news','annonce','courrier','ressource','media','evenement']],
        'finance'  => ['label' => 'Finance',                 'types' => ['facture']],
        'appro'    => ['label' => 'Achats & Moyens Généraux','types' => ['commande','demande','devis','livraison','dysfonc','intervention','immo']],
        'rh'       => ['label' => 'GRH & Paie',              'types' => ['employee','absence','formation']],
        'projet'   => ['label' => 'Projets / Tâches',        'types' => ['projet','tache']],
        'strategie'=> ['label' => 'Stratégie',               'types' => ['objectif','kpi']],
    ];

    public static function userCanShare(?User $user, $model): bool
    {
        if (!$user || !$model) return false;
        if (method_exists($model, 'getAttribute')) {
            $creator = $model->created_by ?? $model->auteur_id ?? null;
            if ($creator && (int) $creator === (int) $user->id) return true;
        }
        if ($model instanceof Media) return (bool) $model->is_public;
        if ($model instanceof Projet) {
            if ((int) ($model->chef_projet_id ?? 0) === (int) $user->id) return true;
            if ((int) ($model->sponsor_id ?? 0) === (int) $user->id) return true;
            return $model->membres()->where('users.id', $user->id)->exists();
        }
        if ($model instanceof Tache) {
            if ((int) ($model->responsable_id ?? 0) === (int) $user->id) return true;
            return $model->assignes()->where('users.id', $user->id)->exists();
        }
        if ($model instanceof Evenement && (bool) ($model->is_public ?? false)) return true;
        // Dysfonctionnement : déclarant ou personne qui l'a pris en charge
        if ($model instanceof Dysfonctionnement) {
            if ((int) ($model->declarant_id ?? 0) === (int) $user->id) return true;
            if ((int) ($model->pris_en_charge_par ?? 0) === (int) $user->id) return true;
        }
        // Intervention : technicien affecté
        if ($model instanceof Intervention) {
            if ((int) ($model->technicien_id ?? 0) === (int) $user->id) return true;
        }
        // CommandeInterne : demandeur ou supérieur valideur
        if ($model instanceof CommandeInterne) {
            if ((int) ($model->demandeur_id ?? 0) === (int) $user->id) return true;
            if ((int) ($model->superieur_id ?? 0) === (int) $user->id) return true;
        }
        // Employee : sa propre fiche
        if ($model instanceof Employee && (int) ($model->user_id ?? 0) === (int) $user->id) return true;
        // Immobilisation / Devis / Livraison / KPI : catalogue général, partageables (données non nominatives)
        if ($model instanceof Immobilisation) return true;
        if ($model instanceof DevisFournisseur) return true;
        if ($model instanceof LivraisonFournisseur) return true;
        if ($model instanceof Kpi) return true;
        // Objectif : responsable
        if ($model instanceof Objectif && (int) ($model->responsable_id ?? 0) === (int) $user->id) return true;
        // Employee / Facture / Absence / Formation / CommandeFournisseur : réservés au créateur (déjà couvert)
        // + HasPublication public s'il existe
        if (method_exists($model, 'publication')) {
            $pub = $model->publication;
            if ($pub && $pub->visibilite === 'public') return true;
        }
        return false;
    }

    public static function normalize($model): array
    {
        $class = get_class($model);
        $meta = self::META[$class] ?? ['label' => class_basename($class), 'icon' => 'fa-file', 'title' => null, 'excerpt' => null, 'route' => null];
        $rawTitle = $meta['title'] ? $model->{$meta['title']} : ($model->title ?? $model->titre ?? $model->nom ?? $model->id);
        // Employee : combine prénoms + noms pour un affichage complet
        if ($model instanceof Employee) {
            $rawTitle = trim(($model->prenoms ?? '').' '.($model->noms ?? ''));
        }
        $title = (string) ($rawTitle ?? '—');
        $excerpt = $meta['excerpt'] ? strip_tags((string) ($model->{$meta['excerpt']} ?? '')) : null;
        $url = null;
        if ($meta['route']) {
            try { $url = route($meta['route'], $model); } catch (\Throwable $e) {}
        }
        return [
            'type'    => array_search($class, self::TYPES, true) ?: 'unknown',
            'id'      => (int) $model->id,
            'label'   => $meta['label'],
            'icon'    => $meta['icon'],
            'title'   => mb_substr($title, 0, 120),
            'excerpt' => $excerpt ? mb_substr($excerpt, 0, 180) : null,
            'url'     => $url,
        ];
    }

    /**
     * Recherche partagée.
     * @param string|null $type  Si fourni, ne cherche QUE dans ce type (ignore $module).
     * @param bool $allowEmpty   Si true et q vide, retourne les $limit derniers items par défaut.
     */
    public static function search(?User $user, string $q, int $limit = 5, string $module = 'all', ?string $type = null, bool $allowEmpty = false): array
    {
        if (!$user) return [];
        $q = trim($q);
        if (!$allowEmpty && mb_strlen($q) < 2) return [];
        // Si type explicite → scope réduit à ce seul type
        $allowedTypes = $type ? [$type] : (self::MODULES[$module]['types'] ?? self::MODULES['all']['types']);
        $results = [];

        $runners = [
            'news' => fn() => self::visibleQuery(News::class, $user, ['title'], $q)->take($limit)->get(),
            'annonce' => fn() => self::visibleQuery(Annonce::class, $user, ['title'], $q)->take($limit)->get(),
            'courrier' => fn() => self::visibleQuery(Courrier::class, $user, ['objet'], $q)->take($limit)->get(),
            'ressource' => fn() => self::visibleQuery(Ressource::class, $user, ['titre'], $q)->take($limit)->get(),
            'media' => fn() => Media::whereIn('type', ['image', 'video'])
                ->where(function ($qq) use ($user) { $qq->where('is_public', true)->orWhere('created_by', $user->id); })
                ->where('titre', 'ilike', "%{$q}%")->take($limit)->get(),
            'evenement' => fn() => Evenement::where('titre', 'ilike', "%{$q}%")
                ->where(function ($qq) use ($user) { $qq->where('is_public', true)->orWhere('created_by', $user->id); })
                ->take($limit)->get(),
            'post' => fn() => Post::where('contenu', 'ilike', "%{$q}%")
                ->where(function ($qq) use ($user) {
                    $qq->where('created_by', $user->id)
                       ->orWhereHas('publication', fn($p) => $p->where('visibilite', 'public'));
                })->take($limit)->get(),

            // Finance : factures liées à l'utilisateur créateur ou visiteur autorisé
            'facture' => fn() => Facture::where(function ($qq) use ($q) {
                    $qq->where('numero', 'ilike', "%{$q}%")->orWhere('objet', 'ilike', "%{$q}%");
                })
                ->where('created_by', $user->id) // scoped au créateur (sensible : montant, tiers)
                ->take($limit)->get(),

            // GRH : own
            'employee'  => fn() => Employee::where('user_id', $user->id) // sa propre fiche
                ->where(function ($qq) use ($q) {
                    $qq->where('noms', 'ilike', "%{$q}%")->orWhere('prenoms', 'ilike', "%{$q}%")->orWhere('matricule', 'ilike', "%{$q}%");
                })->take($limit)->get(),
            'absence'   => fn() => Absence::where('label', 'ilike', "%{$q}%")
                ->where('created_by', $user->id)->take($limit)->get(),
            'formation' => fn() => Formation::where('label', 'ilike', "%{$q}%")
                ->where('created_by', $user->id)->take($limit)->get(),

            // Achats/MG : own uniquement (données comptables sensibles)
            'commande'  => fn() => CommandeFournisseur::where(function ($qq) use ($q) {
                    $qq->where('reference', 'ilike', "%{$q}%")->orWhere('objet', 'ilike', "%{$q}%");
                })->where('created_by', $user->id)->take($limit)->get(),
            'demande'   => fn() => CommandeInterne::where('reference', 'ilike', "%{$q}%")
                ->where(function ($qq) use ($user) {
                    $qq->where('demandeur_id', $user->id)->orWhere('superieur_id', $user->id);
                })->take($limit)->get(),
            'devis'     => fn() => DevisFournisseur::where('reference', 'ilike', "%{$q}%")
                ->take($limit)->get(),
            'livraison' => fn() => LivraisonFournisseur::where('reference', 'ilike', "%{$q}%")
                ->take($limit)->get(),
            'dysfonc'   => fn() => Dysfonctionnement::where('label', 'ilike', "%{$q}%")
                ->where(function ($qq) use ($user) {
                    $qq->where('declarant_id', $user->id)->orWhere('pris_en_charge_par', $user->id);
                })->take($limit)->get(),
            'intervention' => fn() => Intervention::where('label', 'ilike', "%{$q}%")
                ->where('technicien_id', $user->id)->take($limit)->get(),
            'immo'      => fn() => Immobilisation::where('designation', 'ilike', "%{$q}%")
                ->take($limit)->get(), // catalogue général — pas de restriction

            // Projets : own / chef / sponsor / membre
            'projet' => fn() => Projet::where('nom', 'ilike', "%{$q}%")
                ->where(function ($qq) use ($user) {
                    $qq->where('created_by', $user->id)
                       ->orWhere('chef_projet_id', $user->id)
                       ->orWhere('sponsor_id', $user->id)
                       ->orWhereHas('membres', fn($x) => $x->where('users.id', $user->id));
                })->take($limit)->get(),
            'tache' => fn() => Tache::where('titre', 'ilike', "%{$q}%")
                ->where(function ($qq) use ($user) {
                    $qq->where('created_by', $user->id)->orWhere('responsable_id', $user->id)
                       ->orWhereHas('assignes', fn($x) => $x->where('users.id', $user->id));
                })->take($limit)->get(),

            // Stratégie : objectifs dont l'utilisateur est responsable, KPI global (indicateur stratégique visible)
            'objectif' => fn() => Objectif::where('titre', 'ilike', "%{$q}%")
                ->where('responsable_id', $user->id)->take($limit)->get(),
            'kpi' => fn() => Kpi::where('titre', 'ilike', "%{$q}%")
                ->take($limit)->get(),
        ];

        foreach ($allowedTypes as $type) {
            if (!isset($runners[$type])) continue;
            try {
                foreach ($runners[$type]() as $m) {
                    $results[] = self::normalize($m);
                }
            } catch (\Throwable $e) {
                \Log::warning('[ShareableResolver] search '.$type.' error: '.$e->getMessage());
            }
        }
        return $results;
    }

    private static function visibleQuery(string $class, User $user, array $searchColumns, string $q)
    {
        return $class::query()
            ->where(function ($qq) use ($searchColumns, $q) {
                foreach ($searchColumns as $col) $qq->orWhere($col, 'ilike', "%{$q}%");
            })
            ->where(function ($qq) use ($user) {
                $qq->where('created_by', $user->id)
                   ->orWhereHas('publication', fn($p) => $p->where('visibilite', 'public'));
            });
    }

    /**
     * Regex de la syntaxe inline de partage : @_module&@_section&@_titre
     * Modules/sections restent au format slug (alphanum + tirets).
     * Le titre s'étend jusqu'à la fin de ligne ou à un espace + délimiteur `@_` d'une éventuelle 2ᵉ mention.
     */
    public const INLINE_PATTERN = '/@_([a-z0-9_-]+)&@_([a-z0-9_-]+)&@_(.+?)(?=\s*@_|$)/iu';

    /**
     * Parse la première mention `@_mod&@_sec&@_titre` d'un texte.
     * Retourne ['type' => X, 'id' => Y, 'clean' => texte sans la mention] ou null.
     *
     * ⚠️ Sécurité : vérifie userCanShare avant de retourner un id. Un utilisateur
     * ne peut pas indexer un contenu qu'il n'a pas le droit de partager.
     */
    public static function parseInlineShare(?string $content, ?User $user): ?array
    {
        if (!$content || !$user) return null;
        if (!preg_match(self::INLINE_PATTERN, $content, $m)) return null;

        $moduleKey  = strtolower($m[1]);
        $sectionKey = strtolower($m[2]);
        $rawTitle   = trim($m[3]);
        if ($rawTitle === '') return null;

        $type = self::SECTIONS[$moduleKey][$sectionKey] ?? null;
        if (!$type) return null;
        $class = self::TYPES[$type] ?? null;
        if (!$class) return null;

        // Colonne "titre" à interroger selon le meta
        $titleCol = self::META[$class]['title'] ?? null;
        if (!$titleCol) return null;

        // Recherche exacte (ILIKE) sur le titre — max 1 match, priorité à la correspondance stricte
        $model = $class::query()
            ->where($titleCol, 'ilike', $rawTitle)
            ->orderByDesc('id')
            ->first();
        // Fallback : correspondance floue
        if (!$model) {
            $model = $class::query()->where($titleCol, 'ilike', '%'.$rawTitle.'%')->orderByDesc('id')->first();
        }
        if (!$model) return null;

        // Contrôle d'accès
        if (!self::userCanShare($user, $model)) return null;

        $clean = trim(preg_replace(self::INLINE_PATTERN, '', $content, 1));
        return [
            'type'  => $type,
            'id'    => (int) $model->id,
            'clean' => $clean !== '' ? $clean : null,
        ];
    }
}
