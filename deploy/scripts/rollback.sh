#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════
# OptimiZe — Rollback rapide vers un état antérieur
# Usage : bash deploy/scripts/rollback.sh <DB_BACKUP_FILE> [GIT_SHA]
#
# Ex :   bash deploy/scripts/rollback.sh ~/backups/optimize-db-20260907-153000.dump
#        bash deploy/scripts/rollback.sh ~/backups/optimize-db-XXX.dump abc1234
# ═══════════════════════════════════════════════════════════════
set -euo pipefail

PHP_BIN="${PHP_BIN:-/usr/local/bin/ea-php83}"
COMPOSER_BIN="${COMPOSER_BIN:-/opt/cpanel/composer/bin/composer}"
[[ -x "$PHP_BIN" ]] || PHP_BIN=$(command -v php)
[[ -x "$COMPOSER_BIN" ]] || COMPOSER_BIN=$(command -v composer || echo "")

DB_BACKUP="${1:-}"
GIT_SHA="${2:-}"

if [[ -z "$DB_BACKUP" ]]; then
    echo "❌ Usage: $0 <DB_BACKUP_FILE> [GIT_SHA]" >&2
    echo "   Backups disponibles :" >&2
    ls -1t ~/backups/optimize-db-*.dump ~/backups/optimize-db-*.sql.gz 2>/dev/null | head -5 >&2
    exit 1
fi

if [[ ! -f "$DB_BACKUP" ]]; then
    echo "❌ Backup introuvable : $DB_BACKUP" >&2
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "❌ Lancez depuis la racine de l'app (.env introuvable)" >&2
    exit 1
fi

echo "═══════════════════════════════════════════════"
echo "OptimiZe — ROLLBACK"
echo "═══════════════════════════════════════════════"
echo "DB backup : $DB_BACKUP"
[[ -n "$GIT_SHA" ]] && echo "Git SHA   : $GIT_SHA"
echo ""
read -p "⚠️  Confirmer le rollback ? (oui/non) : " CONFIRM
[[ "$CONFIRM" == "oui" ]] || { echo "Annulé."; exit 0; }
echo ""

DB_CONNECTION=$(grep '^DB_CONNECTION=' .env | cut -d= -f2 | tr -d '"')
DB_NAME=$(grep '^DB_DATABASE=' .env | cut -d= -f2 | tr -d '"')
DB_USER=$(grep '^DB_USERNAME=' .env | cut -d= -f2 | tr -d '"')
DB_HOST=$(grep '^DB_HOST=' .env | cut -d= -f2 | tr -d '"')
DB_PORT=$(grep '^DB_PORT=' .env | cut -d= -f2 | tr -d '"')
DB_PASS=$(grep '^DB_PASSWORD=' .env | cut -d= -f2 | tr -d '"')

# 1) MAINTENANCE ─────────────────────────────────────
echo "─── 1/4 Mode maintenance ─────────────────"
$PHP_BIN artisan down --retry=60 || true
echo ""

# 2) RESTAURATION DB ────────────────────────────────
echo "─── 2/4 Restauration DB ──────────────────"
if [[ "$DB_CONNECTION" == "pgsql" ]]; then
    PGPASSWORD="$DB_PASS" \
      pg_restore -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
      --clean --if-exists "$DB_BACKUP"
elif [[ "$DB_CONNECTION" == "mysql" ]]; then
    if [[ "$DB_BACKUP" == *.gz ]]; then
        gunzip -c "$DB_BACKUP" | mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"
    else
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DB_BACKUP"
    fi
else
    echo "❌ SGBD non supporté : $DB_CONNECTION" >&2
    exit 1
fi
echo "✅ DB restaurée"
echo ""

# 3) CHECKOUT GIT + COMPOSER (si SHA fourni) ────────
if [[ -n "$GIT_SHA" ]]; then
    echo "─── 3/4 Checkout $GIT_SHA ────────────────"
    git status --porcelain | grep -q . && git stash push -u -m "pre-rollback-$(date +%Y%m%d-%H%M%S)"
    git checkout "$GIT_SHA"
    if [[ -n "$COMPOSER_BIN" ]]; then
        $COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -5
    fi
    echo ""
fi

# 4) CACHE + UP ─────────────────────────────────────
echo "─── 4/4 Cache rebuild + up ───────────────"
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan up
echo ""

echo "═══════════════════════════════════════════════"
echo "✅ Rollback terminé"
echo "═══════════════════════════════════════════════"
echo ""
echo "Vérifier :"
echo "  - Le site répond : curl -sI \$(grep APP_URL .env | cut -d= -f2)"
echo "  - Aucune erreur récente : tail -30 storage/logs/laravel.log"
