#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════
# OptimiZe — Update production tout-en-un (LWS cPanel)
# Enchaîne : down → backup → git pull → composer → migrate → cache → up
# À lancer depuis la racine de l'app : bash deploy/scripts/update.sh
# ═══════════════════════════════════════════════════════════════
set -euo pipefail

# Configuration ─────────────────────────────────────────
PHP_BIN="${PHP_BIN:-/usr/local/bin/ea-php83}"
COMPOSER_BIN="${COMPOSER_BIN:-/opt/cpanel/composer/bin/composer}"
GIT_BRANCH="${GIT_BRANCH:-main}"
COMPOSER_PROCESS_TIMEOUT="${COMPOSER_PROCESS_TIMEOUT:-900}"
export COMPOSER_PROCESS_TIMEOUT

# Fallback chemins si les binaires cPanel ne sont pas trouvés
[[ -x "$PHP_BIN" ]] || PHP_BIN=$(command -v php)
[[ -x "$COMPOSER_BIN" ]] || COMPOSER_BIN=$(command -v composer || echo "")

echo "═══════════════════════════════════════════════"
echo "OptimiZe — Update $(date +%Y-%m-%d\ %H:%M:%S)"
echo "═══════════════════════════════════════════════"
echo "PHP      : $PHP_BIN ($($PHP_BIN -r 'echo PHP_VERSION;'))"
echo "Composer : $COMPOSER_BIN"
echo "Branche  : $GIT_BRANCH"
echo ""

if [[ ! -f artisan ]]; then
    echo "❌ Lancez depuis la racine de l'app (fichier artisan introuvable)" >&2
    exit 1
fi

# 1) BACKUP ─────────────────────────────────────────────
echo "─── 1/6 Backup ──────────────────────────────────"
bash deploy/scripts/backup.sh
echo ""

# 2) MAINTENANCE ────────────────────────────────────────
echo "─── 2/6 Mode maintenance ────────────────────────"
$PHP_BIN artisan down --retry=60 --render='errors::503'
echo ""

# Rollback automatique si une étape échoue à partir d'ici
trap '$PHP_BIN artisan up; echo "⚠️  Update INTERROMPU — site remis en ligne"' ERR

# 3) GIT PULL ───────────────────────────────────────────
echo "─── 3/6 Git pull ────────────────────────────────"
if [[ -d .git ]]; then
    # Sauvegarder d'éventuels edits locaux avant pull
    if [[ -n "$(git status --porcelain)" ]]; then
        STASH_NAME="prod-local-$(date +%Y%m%d-%H%M%S)"
        echo "→ Modifications locales détectées, stash : $STASH_NAME"
        git stash push -u -m "$STASH_NAME"
    fi
    git fetch origin
    git checkout "$GIT_BRANCH"
    git pull origin "$GIT_BRANCH"
    echo "✅ Code à $(git log -1 --format='%h — %s')"
else
    echo "⚠️  Pas un repo git — étape ignorée. Assurez-vous que le code est à jour (FTP)."
fi
echo ""

# 4) COMPOSER ───────────────────────────────────────────
echo "─── 4/6 Composer install ────────────────────────"
if [[ -n "$COMPOSER_BIN" ]]; then
    $COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -8
    echo "✅ Vendor à jour ($(ls vendor 2>/dev/null | wc -l) packages)"
else
    echo "⚠️  Composer indisponible — étape ignorée"
fi
echo ""

# 5) MIGRATIONS ─────────────────────────────────────────
echo "─── 5/6 Migrations ──────────────────────────────"
PENDING=$($PHP_BIN artisan migrate:status 2>/dev/null | grep -c "Pending" || true)
if [[ "$PENDING" -gt 0 ]]; then
    echo "→ $PENDING migration(s) à appliquer :"
    $PHP_BIN artisan migrate:status | grep "Pending" | head -5
    $PHP_BIN artisan migrate --force
else
    echo "✅ Aucune migration en attente"
fi
echo ""

# 6) CACHE REBUILD + UP ─────────────────────────────────
echo "─── 6/6 Cache rebuild + storage link ────────────"
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache 2>/dev/null || true
$PHP_BIN artisan storage:link 2>&1 | grep -v "already exists" || true

trap - ERR
$PHP_BIN artisan up
echo ""

echo "═══════════════════════════════════════════════"
echo "✅ Update terminé — $(date +%H:%M:%S)"
echo "═══════════════════════════════════════════════"
echo ""
echo "Prochaine étape : lancer la validation post-deploy"
echo "  bash deploy/scripts/healthcheck.sh"
