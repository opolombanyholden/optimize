#!/usr/bin/env bash
# ═════════════════════════════════════════════════════════════
# OptimiZe ERP — Script de déploiement production
# ═════════════════════════════════════════════════════════════
# À exécuter sur le serveur après chaque `git pull`.
# Usage : bash deploy/deploy.sh
# ─────────────────────────────────────────────────────────────
set -euo pipefail

APP_ROOT="${APP_ROOT:-$(cd "$(dirname "$0")/.." && pwd)}"
cd "$APP_ROOT"
echo "→ Déploiement OptimiZe dans $APP_ROOT"

# 1) Maintenance
echo "→ Passage en mode maintenance"
php artisan down --render="errors::503" --retry=60 || true

# 2) Dépendances PHP (prod = sans dev, sans scripts npm)
echo "→ composer install --no-dev --optimize-autoloader"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 3) Assets frontend (si vite est utilisé)
if [ -f package.json ]; then
    echo "→ npm ci && npm run build"
    npm ci --no-audit --prefer-offline
    npm run build
fi

# 4) Cache Laravel : clear + rebuild optimisé
echo "→ artisan optimize:clear"
php artisan optimize:clear

echo "→ Migrations DB (avec force)"
php artisan migrate --force

echo "→ storage:link (idempotent)"
php artisan storage:link || true

echo "→ Cache config + routes + views"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5) Permissions storage + cache
echo "→ Permissions storage & bootstrap/cache"
chmod -R ug+rwX storage bootstrap/cache
# Adapter user:group selon la config Apache/Nginx (ex : www-data:www-data)
# chown -R www-data:www-data storage bootstrap/cache

# 6) Restart queue worker (via Supervisor)
if command -v supervisorctl >/dev/null 2>&1; then
    echo "→ Redémarrage queue worker (supervisorctl)"
    sudo supervisorctl restart optimiz-worker:* || true
fi

# 7) Sortie de maintenance
echo "→ Sortie mode maintenance"
php artisan up

echo "✓ Déploiement terminé"
