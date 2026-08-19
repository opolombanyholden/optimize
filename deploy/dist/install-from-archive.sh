#!/usr/bin/env bash
# ═════════════════════════════════════════════════════════════
# OptimiZe ERP — Installation depuis archive .tar.gz
# ═════════════════════════════════════════════════════════════
# À exécuter sur le serveur, dans le dossier où se trouve
# l'archive uploadée. Idempotent (peut être relancé).
#
# Usage :
#   bash install-from-archive.sh [chemin_archive] [destination]
#
# Exemples :
#   bash install-from-archive.sh optimiz-latest.tar.gz /var/www/optimiz
#   bash install-from-archive.sh optimiz-20260818-143956.tar.gz ~/optimiz  # cPanel
# ─────────────────────────────────────────────────────────────
set -euo pipefail

ARCHIVE="${1:-optimiz-latest.tar.gz}"
DEST="${2:-/var/www/optimiz}"

if [ ! -f "$ARCHIVE" ]; then
    echo "✗ Archive introuvable : $ARCHIVE"
    exit 1
fi

# Détecte le binaire PHP (cPanel utilise ea-php83)
PHP_BIN="php"
if command -v ea-php83 >/dev/null 2>&1; then PHP_BIN="ea-php83"
elif [ -x /usr/local/bin/ea-php83 ]; then PHP_BIN="/usr/local/bin/ea-php83"
elif command -v php8.3 >/dev/null 2>&1; then PHP_BIN="php8.3"
fi
echo "→ PHP binary : $PHP_BIN"

# 1) Vérif checksum si dispo
if [ -f "${ARCHIVE}.sha256" ]; then
    echo "→ Vérification SHA-256"
    shasum -a 256 -c "${ARCHIVE}.sha256" || { echo "✗ Checksum invalide"; exit 1; }
fi

# 2) Prépare le dossier destination
mkdir -p "$DEST"
IS_UPDATE=false
if [ -f "$DEST/artisan" ]; then
    IS_UPDATE=true
    echo "→ Installation existante détectée — mode UPDATE"
    cd "$DEST" && $PHP_BIN artisan down --render="errors::503" --retry=60 2>/dev/null || true
    # Backup rapide de .env avant extraction
    cp "$DEST/.env" "$DEST/.env.backup-$(date +%Y%m%d-%H%M%S)" 2>/dev/null || true
fi

# 3) Extraction
echo "→ Extraction dans $DEST"
tar -xzf "$ARCHIVE" -C "$DEST"

cd "$DEST"

# 4) .env : ne pas écraser un existant
if [ ! -f .env ]; then
    if [ -f deploy/cpanel/.env.cpanel.example ]; then
        cp deploy/cpanel/.env.cpanel.example .env
    elif [ -f deploy/.env.production.example ]; then
        cp deploy/.env.production.example .env
    fi
    echo ""
    echo "⚠️  .env créé à partir du template. À éditer AVANT toute autre étape :"
    echo "   nano $DEST/.env"
    echo "   → renseigner APP_URL, DB_*, MAIL_*"
    echo "   puis : $PHP_BIN artisan key:generate --force"
    echo ""
    echo "Une fois .env prêt, relancer ce script pour finaliser."
    exit 0
fi

# 5) APP_KEY : génère si manquant
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "→ Génération APP_KEY"
    $PHP_BIN artisan key:generate --force
fi

# 6) Permissions
echo "→ Permissions storage + bootstrap/cache"
chmod -R 775 storage bootstrap/cache 2>/dev/null || chmod -R ug+rwX storage bootstrap/cache

# 7) storage:link (idempotent)
$PHP_BIN artisan storage:link 2>/dev/null || true

# 8) Migrations DB
echo "→ Migrations"
$PHP_BIN artisan migrate --force

# 9) Seeders essentiels (uniquement à la première install)
if [ "$IS_UPDATE" = "false" ]; then
    echo "→ Seeders initiaux (roles/permissions)"
    $PHP_BIN artisan db:seed --class=RolePermissionSeeder --force 2>&1 || echo "  (RolePermissionSeeder ignoré — à lancer manuellement si besoin)"
fi

# 10) Cache Laravel
echo "→ Cache Laravel"
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

# 11) Sortie de maintenance
if [ "$IS_UPDATE" = "true" ]; then
    $PHP_BIN artisan up
fi

echo ""
echo "═══════════════════════════════════════════════════"
echo "  ✓ Installation terminée dans $DEST"
echo "═══════════════════════════════════════════════════"
if [ "$IS_UPDATE" = "false" ]; then
    echo ""
    echo "PROCHAINES ÉTAPES :"
    echo "  1. Vérifier que le document root du domaine pointe vers $DEST/public"
    echo "  2. Activer HTTPS (AutoSSL cPanel ou certbot)"
    echo "  3. Créer un compte admin (via seeder ou en direct)"
    echo "  4. Configurer les cron jobs (voir deploy/crontab.example)"
fi
