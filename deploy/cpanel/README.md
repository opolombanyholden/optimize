# OptimiZe ERP — Déploiement sur cPanel / WHM

Guide spécifique pour un hébergement mutualisé/VPS géré via **cPanel** (Apache ou LiteSpeed).

## ⚠️ Vérifications préalables cPanel

Avant de commencer, contrôler dans cPanel :

| Ce qu'il faut | Où le trouver | Notes |
|---|---|---|
| **PHP 8.3** disponible | *Select PHP Version* (ou *MultiPHP Manager*) | Passer aussi les extensions requises (voir plus bas) |
| **PostgreSQL 17** | *PostgreSQL Databases* | Si absent : demander à l'hébergeur ou basculer sur MySQL (impact important — voir section MySQL) |
| **SSH activé** | *SSH Access* | Facultatif mais fortement recommandé pour `composer` et `artisan` |
| **Composer** | Via SSH : `composer --version` | Souvent préinstallé ; sinon voir "Installer Composer sans root" |
| **Node.js** | *Setup Node.js App* (parfois absent) | Optionnel — on peut builder les assets en local et pousser le dossier `public/build/` |
| **Cron Jobs** | *Cron Jobs* | Pour le scheduler Laravel |
| **Sous-domaine ou domaine** | *Subdomains* / *Domains* | Le document root doit pointer vers `public/` |

### Extensions PHP à activer

Dans **Select PHP Version → Extensions**, cocher :
`pdo_pgsql` (ou `pdo_mysql`), `mbstring`, `intl`, `gd`, `zip`, `curl`, `xml` (déjà `dom` + `simplexml`), `bcmath`, `fileinfo`, `openssl`, `tokenizer`, `ctype`, `json`

Dans **Options** :
- `memory_limit` = 512M
- `upload_max_filesize` = 50M
- `post_max_size` = 55M
- `max_execution_time` = 300

## Fichiers fournis dans `deploy/cpanel/`

| Fichier | Rôle |
|---|---|
| `public_htaccess` | À placer dans le document root si celui-ci n'est **PAS** `public/` |
| `htaccess.example` | `.htaccess` Laravel standard (à laisser dans `public/`) |
| `push-deploy-cpanel.sh` | Script rsync via SSH (si SSH activé) |
| `install-cpanel.md` | Cette doc (également disponible en Markdown) |

---

## Voie 1 — Déploiement via Git Version Control (cPanel)

cPanel a une fonction **Git Version Control** qui clone directement un dépôt.

1. **cPanel → Git Version Control → Create**
   - Clone URL : `git@github.com:votre-org/optimiz.git` (ou HTTPS avec token)
   - Repository Path : `/home/cpaneluser/repositories/optimiz`
   - Repository Name : `optimiz`
2. **Manage → Pull or Deploy → Update from Remote** pour récupérer
3. **Deployment** (cPanel utilise `.cpanel.yml` — voir fichier fourni)

### Fichier `.cpanel.yml` (à créer à la racine du repo)

```yaml
---
deployment:
  tasks:
    - export DEPLOYPATH=/home/CPANEL_USER/public_html/
    - /bin/cp -R app bootstrap config database public resources routes storage vendor artisan composer.json composer.lock $DEPLOYPATH
    - /bin/cp -R .env.example $DEPLOYPATH.env.example
    - cd $DEPLOYPATH && /usr/local/bin/ea-php83 artisan migrate --force
    - cd $DEPLOYPATH && /usr/local/bin/ea-php83 artisan config:cache
    - cd $DEPLOYPATH && /usr/local/bin/ea-php83 artisan route:cache
    - cd $DEPLOYPATH && /usr/local/bin/ea-php83 artisan view:cache
```

Puis clic **Deploy HEAD Commit** dans cPanel.

---

## Voie 2 — Déploiement via SSH + rsync (recommandé si SSH dispo)

Depuis votre poste local :

```bash
bash deploy/cpanel/push-deploy-cpanel.sh cpaneluser@monserveur.com /home/cpaneluser/optimiz
```

Le script :
1. Build local Vite + Composer no-dev
2. rsync vers `/home/cpaneluser/optimiz/` (racine hors public_html)
3. Symlinks pour exposer uniquement `/public` sous `public_html/`
4. Cache Laravel

---

## Voie 3 — Déploiement via File Manager / FTP (sans SSH)

Si SSH indisponible :

### A. Préparer l'archive en local

```bash
# Depuis votre poste
cd /Applications/MAMP/htdocs/optimiz
composer install --no-dev --optimize-autoloader
npm ci && npm run build
tar --exclude='.git' --exclude='node_modules' --exclude='tests' \
    --exclude='storage/logs/*' --exclude='storage/framework/cache/*' \
    --exclude='.env' -czf /tmp/optimiz.tar.gz .
```

### B. Uploader et extraire via cPanel

1. **cPanel → File Manager** — naviguer vers `/home/cpaneluser/`
2. Créer un dossier `optimiz` (hors `public_html`)
3. Upload `optimiz.tar.gz` dedans
4. Clic droit → **Extract**

### C. Rediriger le document root

Option 1 (idéal) : **cPanel → Domains** → éditer le document root pour pointer vers `/home/cpaneluser/optimiz/public`

Option 2 (si option 1 refusée par l'hébergeur) : garder `public_html` comme racine et déplacer le contenu de `optimiz/public/` dans `public_html/`, puis éditer `public_html/index.php` :

```php
require __DIR__.'/../optimiz/vendor/autoload.php';
$app = require_once __DIR__.'/../optimiz/bootstrap/app.php';
```

### D. Créer `.env` via File Manager

Cliquer droit sur `.env.example` → Rename → `.env` → Edit → coller la config production (voir `../.env.production.example`)

### E. Terminal cPanel (si dispo, sinon SSH)

```bash
cd /home/cpaneluser/optimiz
php artisan key:generate --force
php artisan storage:link
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Base de données PostgreSQL sur cPanel

1. **cPanel → PostgreSQL Databases**
2. **Create Database** : `cpaneluser_optimiz`
3. **Create User** : `cpaneluser_prod` + mot de passe fort
4. **Add User to Database** : cocher `ALL PRIVILEGES`
5. Dans `.env` :
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=cpaneluser_optimiz
   DB_USERNAME=cpaneluser_prod
   DB_PASSWORD=***
   ```

### Si PostgreSQL non dispo : bascule vers MySQL

Impact **majeur** — plusieurs migrations utilisent des fonctionnalités PostgreSQL (`ilike`, `numeric`, check constraints). Voir `CLAUDE.md` "Pièges connus" :
- `->after()` diff différemment
- `ilike` (case-insensitive) → remplacer par `LIKE UPPER()` avec cast
- Certains scopes (`Emplacement`, `FamilleArticle`) utilisent `ilike`

**Recommandation** : rester sur PostgreSQL, choisir un hébergeur qui l'offre (Infomaniak, o2switch avec plan Perform, hébergeurs VPS cPanel).

---

## Composer sans root sur cPanel

Si `composer` n'est pas dispo globalement :

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar ~/bin/composer   # créer ~/bin si absent + ajouter au PATH
echo 'export PATH=$HOME/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
```

Ensuite `composer install --no-dev --optimize-autoloader`.

---

## Cron jobs cPanel

**cPanel → Cron Jobs → Add New Cron Job** :

| Fréquence | Commande |
|---|---|
| `* * * * *` (chaque minute) | `cd /home/cpaneluser/optimiz && /usr/local/bin/ea-php83 artisan schedule:run >> /dev/null 2>&1` |
| `0 3 * * *` (chaque jour 3h) | `cd /home/cpaneluser/optimiz && /usr/local/bin/ea-php83 artisan session:prune-expired` |
| `0 2 * * *` (backup DB) | `pg_dump -U cpaneluser_prod cpaneluser_optimiz \| gzip > /home/cpaneluser/backups/db-$(date +\%Y\%m\%d).sql.gz` |

Adapter le chemin PHP à votre version : la commande exacte se trouve dans **cPanel → Select PHP Version → en haut** (souvent `/opt/cpanel/ea-php83/root/usr/bin/php`).

---

## Queue worker sur cPanel (workaround)

Supervisor n'existe pas sur cPanel mutualisé. Deux alternatives :

**Option A — Sync driver (le plus simple)** : dans `.env`
```env
QUEUE_CONNECTION=sync
```
Les jobs s'exécutent en ligne dans la requête HTTP. Convient si peu de notifications.

**Option B — Cron worker toutes les minutes** :
```
* * * * * cd /home/cpaneluser/optimiz && /usr/local/bin/ea-php83 artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1
```
Le worker traite pendant 55 s puis s'arrête, relancé la minute suivante par cron.

---

## `.htaccess` document root

Le dossier `public/` de Laravel contient déjà un `.htaccess` fonctionnel. Si l'hébergeur bloque `Options -MultiViews`, remplacer par le fichier `deploy/cpanel/htaccess.example` fourni.

Si vous ne pouvez PAS pointer le document root vers `public/` et devez garder `public_html/` :

1. Copier `deploy/cpanel/public_htaccess` vers `public_html/.htaccess`
2. Le `.htaccess` redirige tout vers `public/index.php` du dossier applicatif

---

## Checklist post-déploiement cPanel

- [ ] `https://votredomaine.com` charge la page de login
- [ ] Connexion admin OK
- [ ] File Manager : `storage/logs/laravel.log` sans erreurs
- [ ] Test upload d'une pièce jointe (permissions `storage/app/public`)
- [ ] Test envoi email (via SMTP cPanel : hostname du serveur, port 465 SSL ou 587 TLS)
- [ ] Cron `schedule:run` fonctionne (vérifier logs cPanel)
- [ ] Backups DB générés dans `~/backups/`

## Sécurité cPanel

- **Toujours** : mot de passe fort DB, forcer HTTPS via cPanel (Force HTTPS Redirect)
- **APP_DEBUG=false** dans `.env` (obligatoire)
- **APP_KEY** générée via `php artisan key:generate --force` (jamais réutilisée)
- Restreindre l'accès `.env` : le `.htaccess` du dossier applicatif doit bloquer
- Activer **cPanel → SSL/TLS Status** (Auto SSL / Let's Encrypt)
- Activer **cPanel → ModSecurity** si dispo
- Sauvegardes cPanel automatiques : vérifier configuration hébergeur
