# OptimiZe ERP — Mise en production

Ce dossier contient tous les fichiers nécessaires pour déployer OptimiZe sur un serveur de production.

> 🎛 **Hébergement cPanel/WHM (mutualisé ou VPS managed)** ? Voir le guide dédié [`cpanel/README.md`](cpanel/README.md) — approche différente (pas de sudo, pas de systemctl, config via panneau).

## Fichiers fournis

| Fichier | Rôle |
|---|---|
| `install-server.sh` | **Bootstrap complet** d'un serveur vierge (Debian/Ubuntu) — installe PHP 8.3, PostgreSQL 17, Nginx, Supervisor, crée la DB, configure firewall |
| `push-deploy.sh` | **Déploiement direct** depuis le poste dev via rsync + SSH (sans git côté serveur) |
| `deploy.sh` | Script bash de déploiement/mise à jour (à lancer sur le serveur, en workflow `git pull`) |
| `.env.production.example` | Template de configuration à copier vers `.env` |
| `nginx.conf.example` | Virtual host Nginx (Laravel 12 + PHP-FPM 8.3 + HTTPS) |
| `supervisor.conf.example` | Programme Supervisor pour le queue worker |
| `crontab.example` | Cron pour le Laravel scheduler + sauvegarde DB |

## Prérequis serveur

| Composant | Version minimale |
|---|---|
| PHP | **8.3** avec extensions : `pdo_pgsql`, `mbstring`, `intl`, `gd`, `zip`, `curl`, `xml`, `bcmath` |
| PostgreSQL | **17** |
| Nginx (ou Apache) | dernière version stable |
| Composer | 2.x |
| Node.js | 20+ (uniquement si vous rebuildez les assets) |
| Supervisor | pour le worker de queue (notifications, mail…) |
| Certbot / Let's Encrypt | pour HTTPS |

Extensions PHP à installer sur Debian/Ubuntu :
```bash
sudo apt install php8.3-cli php8.3-fpm php8.3-pgsql php8.3-mbstring \
  php8.3-intl php8.3-gd php8.3-zip php8.3-curl php8.3-xml php8.3-bcmath
```

## Voies de déploiement — 2 options

**Option A — Déploiement direct (recommandée pour équipes petites)** : depuis votre machine dev, `push-deploy.sh` pousse le code via rsync + SSH sur le serveur. Aucun git côté serveur.

**Option B — Workflow git pull** : le serveur clone le dépôt et vous lancez `deploy.sh` après chaque `git pull`. Adapté aux équipes utilisant CI/CD.

### Bootstrap serveur (une seule fois, quelle que soit l'option)

Sur un serveur vierge Debian 12 / Ubuntu 22.04+, en root :

```bash
# Uploader install-server.sh sur le serveur
scp deploy/install-server.sh root@erp.example.com:/tmp/
ssh root@erp.example.com
sudo bash /tmp/install-server.sh erp.example.com "MonMotDePasseFortDB"
```

Le script installe et configure automatiquement :
- PHP 8.3 + toutes les extensions (via dépôt Sury)
- PostgreSQL 17 (via dépôt PGDG) + création `optimiz_prod` DB + user
- Composer 2, Node.js 20
- Nginx (vhost HTTP temporaire) + Supervisor + Certbot
- UFW (firewall 22/80/443) + Fail2ban
- Réglages PHP-FPM (upload 50 Mo, memory 512M, timeout 300s)

En sortie, un récap affiche les prochaines étapes.

---

## Option A — Déploiement direct depuis votre poste (rsync)

Une fois le serveur bootstrappé, depuis votre machine locale :

```bash
# Premier déploiement (le serveur est vide)
bash deploy/push-deploy.sh deploy@erp.example.com /var/www/optimiz

# .env à créer sur le serveur (une seule fois)
ssh deploy@erp.example.com
cd /var/www/optimiz
cp deploy/.env.production.example .env
nano .env                             # renseigner APP_URL, DB_PASSWORD, mail…
php artisan key:generate --force

# Puis relancer un push local pour appliquer migrations + caches
bash deploy/push-deploy.sh deploy@erp.example.com
```

**Fonctionnement de `push-deploy.sh`** :
1. Build local Vite (`npm run build`) — évite d'installer Node sur le serveur
2. Build local Composer (`composer install --no-dev`) — le vendor est poussé
3. Passe l'app distante en maintenance
4. `rsync -avz --delete` avec exclusions intelligentes (`.git`, `.env`, `node_modules`, `storage/logs`, `storage/framework/*`, `bootstrap/cache/*`, uploads publics…)
5. Sur le serveur (via SSH) : permissions storage, `storage:link`, migrations, cache Laravel complet, restart Supervisor
6. Sortie de maintenance

Pour les déploiements suivants : **une seule commande** :
```bash
bash deploy/push-deploy.sh deploy@erp.example.com
```

**Prérequis SSH** :
```bash
# Sur votre machine locale
ssh-copy-id deploy@erp.example.com   # une seule fois
# Vérifier que l'utilisateur deploy a les droits sudo passwordless :
# echo "deploy ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl, /bin/chown, /bin/chmod" > /etc/sudoers.d/deploy
```

---

## Option B — Workflow git pull

### Installation initiale (première mise en production)

### 1. Préparation du serveur

```bash
# Dossier applicatif
sudo mkdir -p /var/www/optimiz
sudo chown -R $USER:www-data /var/www/optimiz
cd /var/www/optimiz

# Clonage du dépôt
git clone <URL_DEPOT> .

# Composer sans dépendances de dev
composer install --no-dev --optimize-autoloader
```

### 2. Base de données

```bash
sudo -u postgres psql <<EOF
CREATE ROLE optimiz_prod WITH LOGIN PASSWORD 'MOT_DE_PASSE_FORT';
CREATE DATABASE optimiz_prod OWNER optimiz_prod ENCODING 'UTF8';
GRANT ALL PRIVILEGES ON DATABASE optimiz_prod TO optimiz_prod;
EOF
```

### 3. Configuration `.env`

```bash
cp deploy/.env.production.example .env
nano .env                            # renseigner APP_KEY, DB, SMTP, APP_URL…
php artisan key:generate --force
```

### 4. Migrations + seeds initiaux

```bash
php artisan migrate --force
# Seeders essentiels (rôles, permissions, admin) :
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
# Optionnel : référentiels de démo (thématiques MG, familles articles…)
# php artisan db:seed --class=DatabaseSeeder --force
```

Le premier compte admin est disponible via `AdminUserSeeder` (identifiants à retrouver / modifier). **Changer immédiatement le mot de passe.**

### 5. Lien symbolique storage

```bash
php artisan storage:link
```

### 6. Cache Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 7. Permissions filesystem

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache
```

### 8. Nginx + SSL

```bash
sudo cp deploy/nginx.conf.example /etc/nginx/sites-available/optimiz.conf
sudo ln -sf /etc/nginx/sites-available/optimiz.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Certificat Let's Encrypt
sudo certbot --nginx -d erp.example.com
```

### 9. Supervisor (queue worker)

```bash
sudo cp deploy/supervisor.conf.example /etc/supervisor/conf.d/optimiz-worker.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start optimiz-worker:*
```

### 10. Cron (scheduler + backups)

```bash
sudo mkdir -p /var/backups/optimiz
sudo chown www-data:www-data /var/backups/optimiz
sudo crontab -u www-data -e
# Coller le contenu de deploy/crontab.example
```

---

## Déploiements ultérieurs (mise à jour)

À chaque nouveau code poussé :

```bash
cd /var/www/optimiz
git pull origin main
bash deploy/deploy.sh
```

Le script `deploy.sh` :
1. Passe l'application en mode maintenance
2. Installe les dépendances Composer (prod)
3. Rebuilde les assets front (si `package.json` présent)
4. Vide et régénère les caches Laravel
5. Applique les migrations
6. Refait `storage:link` (idempotent)
7. Redémarre le queue worker
8. Sort du mode maintenance

En cas d'erreur : `php artisan up` pour forcer la sortie de maintenance.

---

## Rollback rapide

```bash
cd /var/www/optimiz
git reset --hard <SHA_PRECEDENT>
bash deploy/deploy.sh
# Si migrations DB à annuler : php artisan migrate:rollback --step=X --force
```

---

## Sauvegardes

- **Base** : dump quotidien via cron (voir `crontab.example`) dans `/var/backups/optimiz/db-YYYYMMDD.sql.gz`, rétention 30 jours
- **Fichiers uploads** : `storage/app/public/` — à sauvegarder via `rsync` ou snapshot serveur

Restauration DB :
```bash
gunzip -c /var/backups/optimiz/db-20260818.sql.gz | psql -U optimiz_prod optimiz_prod
```

---

## Checklist de vérification post-déploiement

- [ ] `curl -I https://erp.example.com` retourne 200
- [ ] Connexion admin OK sur `/login`
- [ ] Vérifier `storage/logs/laravel.log` : aucune erreur récente
- [ ] `sudo supervisorctl status` : worker RUNNING
- [ ] `sudo systemctl status nginx php8.3-fpm postgresql` : tous OK
- [ ] Test upload d'une pièce jointe (contrôle permissions storage)
- [ ] Test envoi email (facture, notification)
- [ ] Test création d'un incident MG puis intervention (workflow ticket)

---

## Sécurité recommandée

- `APP_DEBUG=false` dans `.env` (obligatoire — expose la stack en cas d'erreur sinon)
- HTTPS obligatoire, HSTS activé (déjà dans le template Nginx)
- Firewall UFW : n'ouvrir que 22, 80, 443
- SSH par clé uniquement (`PasswordAuthentication no`)
- Fail2ban actif sur SSH + Nginx
- Mise à jour hebdo `apt update && apt upgrade`
- Rotation des logs : logrotate déjà géré par Laravel (`LOG_CHANNEL=daily`, `LOG_DAILY_DAYS=14`)
- Sauvegarde DB testée régulièrement (restauration à blanc trimestrielle)

---

## Contact / support

Développé par **Yubile Technologie** — voir `CLAUDE.md` à la racine pour la documentation technique du code.
