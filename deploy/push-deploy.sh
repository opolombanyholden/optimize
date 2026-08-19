#!/usr/bin/env bash
# ═════════════════════════════════════════════════════════════
# OptimiZe ERP — Déploiement direct (rsync from local)
# ═════════════════════════════════════════════════════════════
# Pousse le code local vers un serveur distant via rsync + SSH,
# puis exécute les commandes post-déploiement à distance.
# Aucune configuration git nécessaire côté serveur.
#
# Usage :
#   bash deploy/push-deploy.sh <user@host> [remote_path]
#
# Exemple :
#   bash deploy/push-deploy.sh deploy@erp.example.com /var/www/optimiz
#
# Prérequis :
#   - SSH par clé configuré (ssh-copy-id) — pas de saisie password
#   - L'utilisateur distant a les droits sudo sur www-data/artisan
#   - Le serveur a déjà été bootstrappé via install-server.sh
# ─────────────────────────────────────────────────────────────
set -euo pipefail

REMOTE="${1:?Usage: push-deploy.sh <user@host> [remote_path]}"
REMOTE_PATH="${2:-/var/www/optimiz}"
LOCAL_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "═══════════════════════════════════════════════════"
echo "  Déploiement direct → $REMOTE:$REMOTE_PATH"
echo "═══════════════════════════════════════════════════"

# 1) Sanity check local
if [ ! -f "$LOCAL_ROOT/artisan" ]; then
    echo "✗ Répertoire Laravel introuvable dans $LOCAL_ROOT"
    exit 1
fi

# 2) Build assets locaux avant push (économise la charge serveur)
if [ -f "$LOCAL_ROOT/package.json" ]; then
    echo "→ Build local Vite (npm run build)"
    cd "$LOCAL_ROOT"
    npm ci --silent
    npm run build
fi

# 3) Vendor : build local aussi (plus rapide qu'un composer install distant)
echo "→ composer install --no-dev en local"
cd "$LOCAL_ROOT"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 4) Vérif SSH
echo "→ Test connexion SSH"
ssh -o BatchMode=yes -o ConnectTimeout=5 "$REMOTE" "echo OK" >/dev/null

# 5) Maintenance ON sur le serveur (si déjà déployé une fois)
echo "→ Maintenance ON (si app existante)"
ssh "$REMOTE" "test -f $REMOTE_PATH/artisan && cd $REMOTE_PATH && php artisan down --render='errors::503' --retry=60 || true"

# 6) Rsync — push atomique
echo "→ rsync (push code)"
rsync -avz --delete \
    --exclude '.git' \
    --exclude '.env' \
    --exclude '.env.*' \
    --exclude 'node_modules' \
    --exclude 'tests' \
    --exclude 'storage/logs/*' \
    --exclude 'storage/framework/cache/*' \
    --exclude 'storage/framework/sessions/*' \
    --exclude 'storage/framework/views/*' \
    --exclude 'storage/app/public' \
    --exclude 'bootstrap/cache/*' \
    --exclude '.phpunit.result.cache' \
    --exclude '.DS_Store' \
    --exclude '*.log' \
    "$LOCAL_ROOT/" "$REMOTE:$REMOTE_PATH/"

# 7) Post-deploy à distance
echo "→ Exécution post-déploiement sur le serveur"
ssh "$REMOTE" bash -s <<REMOTE_CMD
set -euo pipefail
cd $REMOTE_PATH

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

# Storage link (idempotent)
php artisan storage:link 2>/dev/null || true

# Migrations DB
php artisan migrate --force

# Cache Laravel : clear + rebuild
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue worker
if command -v supervisorctl >/dev/null 2>&1; then
    sudo supervisorctl restart optimiz-worker:* 2>/dev/null || true
fi

# Sortie de maintenance
php artisan up
REMOTE_CMD

echo ""
echo "✓ Déploiement terminé — $REMOTE:$REMOTE_PATH"
echo "  → Test : curl -I \$(ssh $REMOTE 'grep APP_URL $REMOTE_PATH/.env | cut -d= -f2 | tr -d \" \"')"
