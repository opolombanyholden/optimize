# Mise à jour production — LWS CloudCP / WHM+cPanel

Guide pas-à-pas pour appliquer une **mise à jour** sur le serveur `rs2755624@web39` (LWS CloudCP, CloudLinux v9.8, PostgreSQL, PHP 8.3).

> **Pour un premier déploiement**, voir plutôt [`README.md`](README.md) (guide générique) et [`cpanel/README.md`](cpanel/README.md) (spécifique cPanel).
> **Pour ce déploiement en particulier** (mise à jour d'une installation qui tourne déjà), suivre ce document.

## Ce qui change entre versions

Vérifiez avant chaque déploiement ce qui a bougé côté code :

```bash
# Sur votre poste dev, depuis la racine du projet
git log --oneline main..HEAD           # commits pas encore sur prod
git diff --stat main..HEAD             # ampleur des changements

# Nouvelles migrations
ls -la database/migrations/ | tail -20

# Nouvelles deps composer
git diff main -- composer.json composer.lock | head -30
```

Pour la version courante (2026-09-07), les changements clés :
- **Composer** : ajout `phpoffice/phpspreadsheet` + `phpoffice/phpword` (multi-format grand-livre)
- **Migrations** : `2026_08_29_100000_create_grades_and_avancements.php` + `2026_08_29_140000_add_avancement_auto_workflow.php`
- **Nouveaux modules** : Guide & Docs, Sources de financement (Finance référentiel), Contrats fournisseurs, Réseau social, Groupes discussion, Notifications
- **Dashboards refondus** : Finance, RH, Achats, Objectifs — palette + KPIs + charts SVG
- **Sidebar** : accordéons pour tous les modules
- **Fixes** : breadcrumbs, points d'entrée, filtre visibilité 3-axes

---

## Vue d'ensemble en 6 étapes

1. **Backup DB + fichiers uploadés** (obligatoire, jamais négociable)
2. **Push du code** — 2 voies possibles (git ou FTP)
3. **Composer install** — récupère les nouvelles libs
4. **Migrations** — 2 nouvelles à appliquer
5. **Cache rebuild** — vider et reconstruire
6. **Validation post-deploy** — checklist smoke tests

Durée totale : ~10-15 minutes pour un update standard.

---

## Étape 1 — Backup préalable

**Toujours** avant toute mise à jour. Utilise `deploy/scripts/backup.sh` :

```bash
# Sur le serveur, connecté en SSH
cd ~/public_html
bash deploy/scripts/backup.sh
```

Ou manuellement :

```bash
DB_NAME=$(grep '^DB_DATABASE=' .env | cut -d= -f2)
DB_USER=$(grep '^DB_USERNAME=' .env | cut -d= -f2)
DB_HOST=$(grep '^DB_HOST=' .env | cut -d= -f2)
DB_PORT=$(grep '^DB_PORT=' .env | cut -d= -f2)
STAMP=$(date +%Y%m%d-%H%M%S)

# Dump DB
PGPASSWORD=$(grep '^DB_PASSWORD=' .env | cut -d= -f2) \
  pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" -F c \
  -f ~/backups/optimize-db-$STAMP.dump

# Tar des fichiers uploadés (storage + public/uploads si utilisé)
mkdir -p ~/backups
tar czf ~/backups/optimize-storage-$STAMP.tar.gz storage/app/ public/uploads/ 2>/dev/null

ls -lh ~/backups/optimize-*-$STAMP.*
```

Résultat attendu : deux fichiers dans `~/backups/` — un `.dump` (DB) et un `.tar.gz` (fichiers).

---

## Étape 2 — Push du code

### Voie A — Auto-deploy via cPanel Git (le `.cpanel.yml` fait tout)

Si le repo Git est configuré dans cPanel (menu **Git Version Control**) :

1. **Sur votre poste dev** :
   ```bash
   git push origin main
   ```

2. **Dans cPanel** → *Git Version Control* → repo `optimize` → onglet **Pull or Deploy** → cliquer **Deploy HEAD Commit**

3. Le fichier `.cpanel.yml` exécute automatiquement :
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan migrate --force`
   - `php artisan storage:link`
   - `php artisan optimize:clear`
   - `config:cache` + `route:cache` + `view:cache` + `event:cache`

4. Passer directement à l'**étape 6** (validation).

### Voie B — Git manuel en SSH (recommandé pour update contrôlé)

```bash
cd ~/public_html
php artisan down --retry=60           # site en maintenance
git fetch origin
git status                            # vérifier qu'il n'y a rien de modifié localement
git pull origin main
```

Si `git status` montre des modifications locales sur le serveur (edits directs, cas rare) : les stasher avant `git pull` :
```bash
git stash push -u -m "prod-local-$(date +%Y%m%d)"
```

### Voie C — Upload FTP (si git indisponible)

Depuis votre poste dev, générez une archive propre :

```bash
# Sur votre poste dev
git archive --format=tar.gz HEAD -o /tmp/optimize-$(date +%Y%m%d).tar.gz

# Upload FTP (via FileZilla ou en CLI)
# Cible : ~/uploads/optimize-XXX.tar.gz sur le serveur
```

Puis sur le serveur :

```bash
cd ~/public_html
php artisan down --retry=60
tar xzf ~/uploads/optimize-XXX.tar.gz -C .
```

⚠️ FTP **écrase** — assurez-vous de ne pas écraser `.env`, `storage/`, `public/uploads/`. L'archive `git archive` ne les contient pas par défaut ; vérifiez avec `tar tzf archive.tar.gz | head`.

---

## Étape 3 — Composer install

Nouvelles libs à installer : **phpoffice/phpspreadsheet** + **phpoffice/phpword**.

```bash
cd ~/public_html

# Composer disponible via cPanel
which composer  ||  ls /opt/cpanel/composer/bin/composer

# Install avec timeout allongé (les libs phpoffice sont grosses)
COMPOSER_PROCESS_TIMEOUT=900 \
  /opt/cpanel/composer/bin/composer install \
  --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -20
```

Si l'install échoue par timeout réseau, réessayer immédiatement — Composer reprend le cache là où il en était.

Après install, régénérer l'autoloader :

```bash
/opt/cpanel/composer/bin/composer dump-autoload -o
```

Vérification :
```bash
php -r "require 'vendor/autoload.php';
  echo 'PhpSpreadsheet: '.(class_exists(PhpOffice\PhpSpreadsheet\Spreadsheet::class)?'OK':'KO')."\n";
  echo 'PhpWord: '.(class_exists(PhpOffice\PhpWord\PhpWord::class)?'OK':'KO')."\n";"
```

Attendu : `PhpSpreadsheet: OK` + `PhpWord: OK`.

---

## Étape 4 — Migrations DB

Vérifier ce qui est pending :

```bash
php artisan migrate:status | grep -i pending
```

Attendu (pour la version 2026-09-07) :
```
2026_08_29_100000_create_grades_and_avancements ....... Pending
2026_08_29_140000_add_avancement_auto_workflow ....... Pending
```

Appliquer :

```bash
php artisan migrate --force
```

Vérification :
```bash
php artisan migrate:status | tail -10   # toutes doivent être Ran
```

En cas d'erreur : voir **Rollback DB** en bas.

---

## Étape 5 — Cache rebuild + storage link

```bash
# 1) Vider tous les caches
php artisan optimize:clear

# 2) Reconstruire optimisé
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 3) Storage link (idempotent — pas d'erreur si déjà en place)
php artisan storage:link 2>&1 | grep -v "already exists"

# 4) Sortir de maintenance
php artisan up
```

---

## Étape 6 — Validation post-deploy

Suivre la checklist : [`checklists/POST_DEPLOY.md`](checklists/POST_DEPLOY.md).

En résumé rapide :

```bash
# 1) Le site répond
curl -sI https://votre-domaine.com/ | head -3

# 2) Login page charge
curl -sI https://votre-domaine.com/login | head -3

# 3) Aucun 500 dans les logs
tail -50 storage/logs/laravel.log
```

Ouvrir le site en navigation privée et vérifier :
- Login fonctionne
- Workspace picker s'affiche (avec 3 espaces principaux dont **Guide & Docs**)
- `/finance/execution-budgetaire` s'affiche
- `/finance/grand-livre` propose le dropdown Export CSV/XLSX/PDF/DOCX
- `/appro/dashboard`, `/rh`, `/objectifs`, `/projet` affichent leurs nouveaux dashboards
- `/docs` liste 6 guides disponibles (Achats, Finance, RH, Intranet, Projet, Stratégie)

---

## Rollback rapide

Si un problème bloquant survient après update :

```bash
# 1) Remettre en maintenance
php artisan down

# 2) Restaurer la DB depuis le backup fait à l'étape 1
BACKUP=~/backups/optimize-db-XXX.dump   # le fichier créé à l'étape 1
PGPASSWORD=$(grep '^DB_PASSWORD=' .env | cut -d= -f2) \
  pg_restore -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
  --clean --if-exists "$BACKUP"

# 3) Revenir au commit précédent
git log --oneline -5                   # identifier le SHA d'avant
git checkout <SHA_PRÉCÉDENT>

# 4) Restaurer composer.lock cohérent
composer install --no-dev --optimize-autoloader

# 5) Rebuild caches + up
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

Ou utiliser le script prêt-à-l'emploi :
```bash
bash deploy/scripts/rollback.sh ~/backups/optimize-db-XXX.dump <SHA_PRÉCÉDENT>
```

---

## Configuration cron (une fois)

Si pas déjà en place, ajouter dans **cPanel → Cron Jobs** :

```
* * * * * cd /home/rs2755624/public_html && /usr/local/bin/ea-php83 artisan schedule:run >> /dev/null 2>&1
```

Vérifier : `crontab -l | grep optimize`.

---

## Sécurité — checklist express

- [ ] `.env` : `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
- [ ] Permissions : `chmod -R 775 storage bootstrap/cache` (et `chown` groupe web)
- [ ] Backup automatisé : cron quotidien via `deploy/scripts/backup.sh`
- [ ] SSL actif (certificat cPanel → *SSL/TLS Status*)
- [ ] `APP_KEY` défini (`php artisan key:generate --show` si vide)
- [ ] `.env` **hors document root** (déjà le cas si structure standard Laravel)

---

## Aide-mémoire — commandes fréquentes

| Besoin | Commande |
|---|---|
| Vider tous les caches | `php artisan optimize:clear` |
| Reconstruire cache prod | `php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache` |
| Voir les logs récents | `tail -100 storage/logs/laravel.log` |
| État migrations | `php artisan migrate:status` |
| Rollback dernière migration | `php artisan migrate:rollback --step=1 --force` |
| Site en maintenance | `php artisan down --retry=60` |
| Site en ligne | `php artisan up` |
| Nb classes autoloader | `composer dump-autoload -o` (regénère) |
| Chemin PHP CLI | `/usr/local/bin/ea-php83` |
| Chemin composer | `/opt/cpanel/composer/bin/composer` |
