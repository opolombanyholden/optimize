#!/usr/bin/env bash
# ═════════════════════════════════════════════════════════════
# OptimiZe ERP — Déploiement direct cPanel via rsync + SSH
# ═════════════════════════════════════════════════════════════
# Suppose que SSH est activé sur le compte cPanel.
# Structure attendue sur le serveur :
#   /home/CPANEL_USER/optimiz/         → application Laravel
#   /home/CPANEL_USER/public_html/     → redirige vers ../optimiz/public/
#
# Usage :
#   bash deploy/cpanel/push-deploy-cpanel.sh <user@host> [remote_app_path]
#
# Exemple :
#   bash deploy/cpanel/push-deploy-cpanel.sh cpaneluser@server.com /home/cpaneluser/optimiz
# ─────────────────────────────────────────────────────────────
set -euo pipefail

REMOTE="${1:?Usage: push-deploy-cpanel.sh <user@host> [remote_path]}"
REMOTE_APP="${2:-/home/$(echo "$REMOTE" | cut -d@ -f1)/optimiz}"
LOCAL_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

# Détection PHP path cPanel (généralement /usr/local/bin/ea-php83)
PHP_BIN_DEFAULT="/usr/local/bin/ea-php83"
PHP_BIN="${PHP_BIN:-$PHP_BIN_DEFAULT}"

echo "═══════════════════════════════════════════════════"
echo "  cPanel deploy → $REMOTE:$REMOTE_APP"
echo "  PHP binary   : $PHP_BIN"
echo "═══════════════════════════════════════════════════"

# 1) Vérif locale
[ -f "$LOCAL_ROOT/artisan" ] || { echo "✗ Laravel introuvable ($LOCAL_ROOT)"; exit 1; }

# 2) Build assets locaux
if [ -f "$LOCAL_ROOT/package.json" ]; then
    echo "→ Build local (Vite)"
    cd "$LOCAL_ROOT"
    npm ci --silent
    npm run build
fi

# 3) Vendor local (évite composer côté cPanel qui peut être lent/limité)
echo "→ composer install --no-dev (local)"
cd "$LOCAL_ROOT"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 4) Test SSH
echo "→ Test SSH"
ssh -o BatchMode=yes -o ConnectTimeout=10 "$REMOTE" "echo OK" >/dev/null

# 5) Maintenance ON si app existante
echo "→ Maintenance ON (si app déjà déployée)"
ssh "$REMOTE" "test -f $REMOTE_APP/artisan && cd $REMOTE_APP && $PHP_BIN artisan down --render='errors::503' --retry=60 || true"

# 6) Rsync — push code
echo "→ rsync"
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
    "$LOCAL_ROOT/" "$REMOTE:$REMOTE_APP/"

# 7) Post-deploy (sans sudo — impossible sur cPanel mutualisé)
echo "→ Post-deploy à distance"
ssh "$REMOTE" bash -s <<REMOTE_CMD
set -euo pipefail
cd $REMOTE_APP

# Permissions (chmod uniquement, chown impossible sans root)
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
find storage -type f -exec chmod 644 {} \; 2>/dev/null || true
find storage -type d -exec chmod 755 {} \; 2>/dev/null || true

# storage:link idempotent
$PHP_BIN artisan storage:link 2>/dev/null || true

# Migrations DB
$PHP_BIN artisan migrate --force

# Cache Laravel
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

# Symlink public_html → app/public (si absent)
if [ ! -e "\$HOME/public_html/index.php" ] || [ -L "\$HOME/public_html" ]; then
    if [ ! -e "\$HOME/public_html" ]; then
        ln -s $REMOTE_APP/public "\$HOME/public_html"
        echo "  ✓ Symlink public_html → $REMOTE_APP/public créé"
    fi
fi

# Sortie de maintenance
$PHP_BIN artisan up
REMOTE_CMD

echo ""
echo "✓ Déploiement cPanel terminé — $REMOTE:$REMOTE_APP"
echo ""
echo "Notes :"
echo "  • Si HTTPS pas encore activé : cPanel → SSL/TLS Status → Run AutoSSL"
echo "  • Cron scheduler à ajouter dans cPanel → Cron Jobs (voir README.md)"
echo "  • .env : copier .env.production.example → .env sur le serveur et remplir"
