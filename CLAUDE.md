# OptimiZe — Guide projet pour Claude Code

> ERP modulaire intégré développé par Yubile Technologie. Stack Laravel 12 + Bootstrap 5.

## Vue d'ensemble

OptimiZe est une **solution de gestion intégrée d'entreprise** combinant un intranet collaboratif et plusieurs sous-ensembles ERP. La cible est l'usage interne pour des organisations africaines (devise par défaut XAF).

Le projet est à **~85% complet**. Modules core fonctionnels, quelques modules en cours.

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | Laravel 12 (PHP 8.3) |
| Base de données | **PostgreSQL 17** (migré depuis MySQL le 2026-05-08) |
| Frontend | Blade + Bootstrap 5 + Font Awesome 6 + Inter font |
| Permissions | Spatie Laravel-Permission |
| Multi-select / search | Tom Select 2.3 |
| WYSIWYG | Quill 2.0 |
| Calendrier | FullCalendar 6 |
| Drag & drop | SortableJS |
| Server local | MAMP (Apache port 8888) ou `php artisan serve` (port 8000) |

### Connexion DB
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=optimiz
DB_USERNAME=optimiz
DB_PASSWORD=passe@2026
```

Backup MySQL initial : `storage/backups/mysql_optimiz_*.sql`

## Architecture des sous-ensembles

```
optimiz/
├── INTRANET (portal central)
│   ├── Communication : Annonces, News
│   ├── Agenda : Calendrier, Événements
│   ├── CRM : Contacts, Organisations, Opportunités, Pipeline
│   ├── Documents & GED : Courrier, Ressources, Médiathèque, Archives, Templates
│   ├── Connaissances : Wiki
│   └── Annuaire : Collaborateurs, Services, Équipes
│
├── ERP sous-ensembles (modules autonomes)
│   ├── Finance & Budget       (✅ complet)
│   ├── RH & Paiement          (✅ complet)
│   ├── Achat & Approvisionnement (✅ complet)
│   ├── Moyens Généraux        (✅ complet)
│   ├── Projets/Tâches PMP     (🟡 quasi complet)
│   └── Objectifs & KPI        (🔴 à démarrer)
│
└── ADMINISTRATION
    ├── Organisations
    ├── Utilisateurs / Rôles / Permissions
    └── Paramétrage (rôles projet, statuts, etc.)
```

## Conventions clés

### Routes
- Préfixes par sous-ensemble : `intranet.*`, `projet.*`, `finance.*`, `rh.*`, `appro.*`, `mg.*`, `admin.*`
- Resources : utiliser `->parameters(['taches' => 'tache'])` quand le pluriel français est tronqué par Laravel (ex: `taches` → `{tach}` par défaut)

### Modèles polymorphiques
4 systèmes polymorphiques existent et sont réutilisables :
- **HasPiecesJointes** (table `intranet_pieces_jointes`) — médias attachables à n'importe quel modèle
- **HasValidation** (tables `intranet_valideurs` + `intranet_historique_validations`) — workflow de clôture pour Projet/Phase/Jalon/Tâche
- **HasProtection** (table `intranet_demandes_modification`) — verrouillage des entités avec contenu, demandes de modif/suppression nécessitent approbation super-admin
- **HasCommentaires** + **HasLikes** + **HasVues** + **HasPublication** — interactions sociales

### Cascade calculée (PMP)
Avancement et coûts cascadent automatiquement :
```
Tâches (avec ponderation %) → Phase (cout_estime/cout_reel) → Projet (budget_consomme)
```
Les méthodes : `Projet::recalculerAvancement()`, `ProjetPhase::cout_estime`, `Tache::cout_total_reel`.

### Statuts ERP legacy
Les anciennes tables ERP (`absences`, `commande_fournisseurs`, `dysfonctionnements`) utilisent **`statut` en INTEGER** (1 = en attente / en cours / ouvert), pas en string. PostgreSQL est strict — toujours passer un int.

### Liens optionnels
- Tâche peut être liée à un projet (`projet_id` nullable) ou autonome
- Tâche peut être liée à un objectif stratégique (`objectif_id` nullable)
- Projet peut être lié à un objectif stratégique (`objectif_id` nullable)
- Toujours utiliser `?->` pour ces relations dans les vues

## Patterns Blade utilisés

### Partials réutilisables
- `intranet._partials.media-display` — affichage médias avec lightbox
- `intranet._partials.media-section` — upload (multipart/form-data requis)
- `intranet._partials.lightbox` — zoom images plein écran
- `intranet._partials.publication-section` + `publication-scripts` — visibilité (public/privé/cibles)
- `projet._partials.projet-header` — tabs navigation PMP
- `projet._partials.modales-cloture` — workflow validation
- `projet._partials.modales-protection` — demandes modif/suppression

### Sidebar contextuel
Variable `$mod` détectée par `request()->routeIs(...)` dans `resources/views/layouts/partials/sidebar.blade.php`. Quand on ajoute des routes dans un nouveau sous-ensemble, mettre à jour la détection.

### Tom Select dans modales Bootstrap
- Toujours `dropdownParent: 'body'` pour éviter le clipping
- Initialiser sur l'événement `shown.bs.modal` (pas `DOMContentLoaded`) pour éviter problèmes de dimensions
- z-index `.ts-dropdown` doit être > z-index modale (≥ 10060)

## Comptes de démo

| Email | Password | Rôle |
|---|---|---|
| admin@optimize.local | password | super-admin |
| test@optimize.local | password | user |

## Commandes fréquentes

```bash
# Reset complet DB + données démo
php artisan migrate:fresh --seed
php artisan db:seed --class=ProjetDemoSeeder

# Vider les caches
php artisan view:clear && php artisan route:clear && php artisan config:clear

# Server dev
php artisan serve  # port 8000 par défaut
```

## Tests

⚠️ **Aucun test automatisé pour le moment** (P0 du backlog). Quand on en ajoute :
- Pest preferé à PHPUnit pour la lisibilité
- Tester d'abord le workflow critique : auth, création projet, validation clôture, cascade avancement

## Documentation source

- `conception/CAHIER_DES_CHARGES_GLOBAL_ERP_OPTIMIZE.md` — spec fonctionnelle complète
- `conception/cahier_conception_Gestion_Projet_PMP_13042026.docx` — détails PMP
- `conception/cahier_conception_Strategie_Gouvernance_13042026.docx` — gouvernance & objectifs

## Pièges connus à éviter

1. **Ne pas mocker** la DB dans les tests — toujours hit PostgreSQL réel (les check constraints comptent)
2. **`->after()` ignoré sur PostgreSQL** — l'ordre des colonnes ne change pas, c'est OK
3. **Enums** : déclarés en MySQL `enum(...)`, convertis en `varchar + check constraint` côté Postgres. Les valeurs sont strict (case + accents)
4. **Slugs auto** : générés dans `Tache::booted()` — toujours unique
5. **Soft deletes** : actifs sur Projet, Phase, Tâche — utiliser `withTrashed()` si besoin

## Roadmap actuelle (priorités)

1. 🔴 Annuaire (Collaborateurs/Services/Équipes) — en cours
2. 🔴 Objectifs & KPI — à démarrer
3. 🟡 Tests Pest — à mettre en place
4. 🟡 Compléter `TacheProjetController` (CRUD complet)
5. 🟢 Messagerie pro — différé
6. 🟢 Scan carte de visite OCR — différé
