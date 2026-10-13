#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════
# OptimiZe — Pré-vérification serveur pour la version « Messagerie »
# (Mails pro IMAP/SMTP, messagerie instantanée, signatures, pièces jointes)
# À lancer AVANT le déploiement, depuis la racine de l'app :
#     bash deploy/scripts/precheck-mails.sh
# Ne modifie rien : lecture seule.
# ═══════════════════════════════════════════════════════════════
set -uo pipefail

PHP_BIN="${PHP_BIN:-/usr/local/bin/ea-php83}"
[[ -x "$PHP_BIN" ]] || PHP_BIN=$(command -v php)

FAIL=0
ok()   { echo "  ✅ $1"; }
warn() { echo "  ⚠️  $1"; }
fail() { echo "  ❌ $1"; FAIL=$((FAIL+1)); }

echo "═══════════════════════════════════════════════"
echo "OptimiZe — Pré-vérification Messagerie ($PHP_BIN)"
echo "═══════════════════════════════════════════════"

# ── 1) Extensions PHP requises (webklex/php-imap, signatures HTML, pièces jointes)
echo ""
echo "─── Extensions PHP ──────────────────────────"
for ext in openssl json mbstring iconv libxml dom zip fileinfo pdo_pgsql; do
    if [[ "$($PHP_BIN -r "echo extension_loaded('${ext}') ? 1 : 0;")" == "1" ]]; then ok "ext: $ext"; else fail "ext manquante: $ext (cPanel → Select PHP Version → Extensions)"; fi
done
# ext-imap n'est plus nécessaire (remplacée par webklex/php-imap en PHP pur)

# ── 2) Limites d'upload (pièces jointes : 20 Mo par fichier, 25 Mo par mail)
echo ""
echo "─── Limites PHP ─────────────────────────────"
$PHP_BIN -r '
function o($v){ $v=trim($v); $u=strtolower(substr($v,-1)); $n=(float)$v; return $u==="g"?$n*1073741824:($u==="m"?$n*1048576:($u==="k"?$n*1024:$n)); }
$u = ini_get("upload_max_filesize"); $p = ini_get("post_max_size"); $m = ini_get("memory_limit");
echo (o($u) >= 20*1048576 ? "  ✅" : "  ❌")." upload_max_filesize = $u (20M minimum)\n";
echo (o($p) >= 26*1048576 ? "  ✅" : "  ❌")." post_max_size = $p (26M minimum)\n";
echo ($m === "-1" || o($m) >= 256*1048576 ? "  ✅" : "  ⚠️ ")." memory_limit = $m (256M conseillé)\n";
'

# ── 3) Connexions sortantes IMAP / SMTP (souvent filtrées sur les hébergements mutualisés)
echo ""
echo "─── Ports sortants (IMAP / SMTP) ────────────"
$PHP_BIN -r '
$cibles = [
  ["ssl://imap.gmail.com", 993, "IMAP SSL 993"],
  ["tcp://outlook.office365.com", 143, "IMAP STARTTLS 143"],
  ["ssl://smtp.gmail.com", 465, "SMTP SSL 465"],
  ["tcp://smtp.office365.com", 587, "SMTP STARTTLS 587"],
];
$ctx = stream_context_create(["ssl" => ["verify_peer" => true, "verify_peer_name" => true]]);
foreach ($cibles as [$h, $p, $l]) {
  $t = microtime(true);
  $s = @stream_socket_client("$h:$p", $en, $es, 8, STREAM_CLIENT_CONNECT, $ctx);
  if ($s) { $b = trim((string) fgets($s)); fclose($s); printf("  ✅ %-18s %s (%.1fs) %s\n", $l, $h, microtime(true)-$t, substr($b, 0, 40)); }
  else    { printf("  ❌ %-18s %s bloqué : %s\n", $l, $h, $es ?: "délai dépassé"); }
}'
echo "  ℹ️  Si un port est bloqué : demander au support LWS d'ouvrir les connexions sortantes vers 993/143/465/587."

# ── 4) Stockage des pièces jointes temporaires
echo ""
echo "─── Stockage ────────────────────────────────"
mkdir -p storage/app/private/mail-pieces 2>/dev/null
[[ -w storage/app/private ]] && ok "storage/app/private inscriptible" || fail "storage/app/private non inscriptible (chmod -R 775 storage)"
[[ -w storage/framework/cache ]] && ok "storage/framework/cache inscriptible" || fail "storage/framework/cache non inscriptible"

# ── 5) Clé d'application (les mots de passe des comptes mail sont chiffrés avec APP_KEY)
echo ""
echo "─── APP_KEY ─────────────────────────────────"
if grep -q '^APP_KEY=base64:' .env 2>/dev/null; then ok "APP_KEY défini — NE JAMAIS le régénérer (mots de passe mail chiffrés)"; else fail "APP_KEY absent de .env"; fi

echo ""
if [[ $FAIL -eq 0 ]]; then echo "✅ Serveur prêt pour le déploiement."; else echo "❌ $FAIL point(s) bloquant(s) à corriger avant de déployer."; fi
exit $FAIL
