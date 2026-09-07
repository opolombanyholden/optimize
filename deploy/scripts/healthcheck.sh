#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════
# OptimiZe — Healthcheck post-déploiement
# À lancer depuis la racine de l'app : bash deploy/scripts/healthcheck.sh
# ═══════════════════════════════════════════════════════════════
set -uo pipefail

PHP_BIN="${PHP_BIN:-/usr/local/bin/ea-php83}"
[[ -x "$PHP_BIN" ]] || PHP_BIN=$(command -v php)

FAIL=0
ok()   { echo "  ✅ $1"; }
warn() { echo "  ⚠️  $1"; }
fail() { echo "  ❌ $1"; FAIL=$((FAIL+1)); }

echo "═══════════════════════════════════════════════"
echo "OptimiZe — Healthcheck $(date +%H:%M:%S)"
echo "═══════════════════════════════════════════════"

if [[ ! -f artisan ]]; then
    fail "artisan introuvable — lancez depuis la racine de l'app"
    exit 1
fi

# ── 1) Fichiers critiques ─────────────────────────────
echo ""
echo "─── Fichiers critiques ───────────────────────"
[[ -f .env ]]                       && ok ".env présent"                       || fail ".env manquant"
[[ -d vendor ]]                     && ok "vendor/ présent"                    || fail "vendor/ manquant — lancer composer install"
[[ -f vendor/autoload.php ]]        && ok "autoload.php présent"               || fail "autoload.php manquant"
[[ -L public/storage ]] && ok "storage link OK" || warn "storage link absent — lancer: php artisan storage:link"

# ── 2) Configuration .env ─────────────────────────────
echo ""
echo "─── Configuration .env ──────────────────────"
APP_ENV=$(grep '^APP_ENV=' .env | cut -d= -f2 | tr -d '"')
APP_DEBUG=$(grep '^APP_DEBUG=' .env | cut -d= -f2 | tr -d '"')
APP_KEY=$(grep '^APP_KEY=' .env | cut -d= -f2 | tr -d '"')
APP_URL=$(grep '^APP_URL=' .env | cut -d= -f2 | tr -d '"')

[[ "$APP_ENV" == "production" ]]    && ok "APP_ENV=production"                 || fail "APP_ENV n'est pas 'production' (=$APP_ENV)"
[[ "$APP_DEBUG" == "false" ]]       && ok "APP_DEBUG=false"                    || fail "APP_DEBUG=$APP_DEBUG (doit être false en prod)"
[[ -n "$APP_KEY" ]]                 && ok "APP_KEY défini"                     || fail "APP_KEY vide — lancer: php artisan key:generate"
[[ "$APP_URL" == https://* ]]       && ok "APP_URL=$APP_URL"                   || warn "APP_URL n'est pas en HTTPS (=$APP_URL)"

# ── 3) PHP + extensions ────────────────────────────────
echo ""
echo "─── PHP + extensions ────────────────────────"
PHP_VERSION=$($PHP_BIN -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if [[ "$PHP_VERSION" == "8.3" || "$PHP_VERSION" == "8.4" ]]; then
    ok "PHP $PHP_VERSION"
else
    warn "PHP $PHP_VERSION (Laravel 12 recommande 8.3)"
fi

for ext in pdo_pgsql mbstring intl gd zip curl xml bcmath fileinfo openssl; do
    if $PHP_BIN -m | grep -qi "^${ext}$"; then ok "ext: $ext"; else fail "ext manquante: $ext"; fi
done

# ── 4) Composer + libs critiques ──────────────────────
echo ""
echo "─── Dépendances Composer ────────────────────"
$PHP_BIN -r "require 'vendor/autoload.php';
    function check(\$c, \$l) { echo class_exists(\$c) ? \"  ✅ \$l\\n\" : \"  ❌ \$l MANQUANT\\n\"; }
    check('Illuminate\\Foundation\\Application', 'Laravel');
    check('PhpOffice\\PhpSpreadsheet\\Spreadsheet', 'PhpSpreadsheet (export XLSX)');
    check('PhpOffice\\PhpWord\\PhpWord', 'PhpWord (export DOCX)');
    check('Barryvdh\\DomPDF\\Facade\\Pdf', 'DomPDF (guide PDF)');
    check('Spatie\\Permission\\Models\\Role', 'Spatie Permission');
" 2>&1

# ── 5) Base de données ────────────────────────────────
echo ""
echo "─── Base de données ─────────────────────────"
DB_OK=$($PHP_BIN -r "
try {
    require 'vendor/autoload.php';
    \$app = require 'bootstrap/app.php';
    \$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
    \$pdo = DB::connection()->getPdo();
    echo 'OK|'.DB::connection()->getDatabaseName();
} catch (\\Throwable \$e) { echo 'ERR|'.\$e->getMessage(); }
" 2>&1)
if [[ "$DB_OK" == OK\|* ]]; then
    ok "Connexion DB : ${DB_OK#OK|}"
else
    fail "Connexion DB : ${DB_OK#ERR|}"
fi

PENDING=$($PHP_BIN artisan migrate:status 2>/dev/null | grep -c "Pending" || echo 0)
if [[ "$PENDING" -eq 0 ]]; then
    ok "Aucune migration en attente"
else
    fail "$PENDING migration(s) en attente — lancer: php artisan migrate --force"
fi

# ── 6) Cache Laravel ──────────────────────────────────
echo ""
echo "─── Cache Laravel ────────────────────────────"
[[ -f bootstrap/cache/config.php ]] && ok "config caché"                       || warn "config non caché — lancer: php artisan config:cache"
[[ -f bootstrap/cache/routes-v7.php ]] && ok "routes cachées"                  || warn "routes non cachées — lancer: php artisan route:cache"
[[ -d storage/framework/views ]] && ls storage/framework/views/*.php > /dev/null 2>&1 && ok "vues compilées" || warn "vues non compilées — lancer: php artisan view:cache"

# ── 7) Permissions écriture ───────────────────────────
echo ""
echo "─── Permissions écriture ────────────────────"
[[ -w storage ]]        && ok "storage/ writable"          || fail "storage/ non writable"
[[ -w bootstrap/cache ]] && ok "bootstrap/cache/ writable"  || fail "bootstrap/cache/ non writable"

# ── 8) Mode maintenance ────────────────────────────────
echo ""
echo "─── Statut application ──────────────────────"
if $PHP_BIN artisan about --only=environment 2>/dev/null | grep -qi "maintenance mode.*enabled"; then
    warn "SITE EN MAINTENANCE — lancer: php artisan up"
else
    ok "Site EN LIGNE"
fi

# ── 9) Logs récents ────────────────────────────────────
echo ""
echo "─── Erreurs récentes (10 dernières lignes ERROR) ──"
if [[ -f storage/logs/laravel.log ]]; then
    RECENT_ERRORS=$(grep -c "ERROR\|CRITICAL\|EMERGENCY" storage/logs/laravel.log 2>/dev/null || echo 0)
    if [[ "$RECENT_ERRORS" -eq 0 ]]; then
        ok "Aucune erreur dans laravel.log"
    else
        warn "$RECENT_ERRORS erreurs total (voir dernières ci-dessous)"
        grep -E "ERROR|CRITICAL|EMERGENCY" storage/logs/laravel.log | tail -3 | sed 's/^/    /'
    fi
fi

# ── VERDICT ───────────────────────────────────────────
echo ""
echo "═══════════════════════════════════════════════"
if [[ "$FAIL" -eq 0 ]]; then
    echo "✅ Healthcheck OK — aucune erreur bloquante"
    exit 0
else
    echo "❌ $FAIL erreur(s) bloquante(s) — voir ci-dessus"
    exit 1
fi
