#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════
# OptimiZe — Backup production (DB PostgreSQL + fichiers uploadés)
# À lancer depuis la racine de l'app : bash deploy/scripts/backup.sh
# ═══════════════════════════════════════════════════════════════
set -euo pipefail

# Répertoire de destination des backups (créé si absent)
BACKUP_DIR="${BACKUP_DIR:-$HOME/backups}"
mkdir -p "$BACKUP_DIR"

# Horodatage unique
STAMP=$(date +%Y%m%d-%H%M%S)

# Charger les variables DB depuis .env
if [[ ! -f .env ]]; then
    echo "❌ Fichier .env introuvable. Lancez ce script depuis la racine de l'app." >&2
    exit 1
fi

DB_CONNECTION=$(grep '^DB_CONNECTION=' .env | cut -d= -f2 | tr -d '"')
DB_NAME=$(grep '^DB_DATABASE=' .env | cut -d= -f2 | tr -d '"')
DB_USER=$(grep '^DB_USERNAME=' .env | cut -d= -f2 | tr -d '"')
DB_HOST=$(grep '^DB_HOST=' .env | cut -d= -f2 | tr -d '"')
DB_PORT=$(grep '^DB_PORT=' .env | cut -d= -f2 | tr -d '"')
DB_PASS=$(grep '^DB_PASSWORD=' .env | cut -d= -f2 | tr -d '"')

echo "═══════════════════════════════════════════════"
echo "OptimiZe — Backup $STAMP"
echo "═══════════════════════════════════════════════"
echo "Cible : $BACKUP_DIR"
echo "SGBD  : $DB_CONNECTION"
echo ""

# ── DB DUMP ────────────────────────────────────────────
DB_FILE=""
if [[ "$DB_CONNECTION" == "pgsql" ]]; then
    DB_FILE="$BACKUP_DIR/optimize-db-$STAMP.dump"
    echo "→ Dump PostgreSQL vers $DB_FILE ..."
    PGPASSWORD="$DB_PASS" \
      pg_dump -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
      -F c -f "$DB_FILE"
elif [[ "$DB_CONNECTION" == "mysql" ]]; then
    DB_FILE="$BACKUP_DIR/optimize-db-$STAMP.sql.gz"
    echo "→ Dump MySQL vers $DB_FILE ..."
    mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" \
      --single-transaction --routines --triggers "$DB_NAME" | gzip > "$DB_FILE"
else
    echo "❌ SGBD non supporté par ce script : $DB_CONNECTION" >&2
    exit 1
fi

DB_SIZE=$(du -h "$DB_FILE" | cut -f1)
echo "✅ DB     : $DB_FILE ($DB_SIZE)"

# ── FICHIERS UPLOADÉS ─────────────────────────────────
FILES_TAR="$BACKUP_DIR/optimize-storage-$STAMP.tar.gz"
echo "→ Tar storage/ + public/uploads/ vers $FILES_TAR ..."
tar czf "$FILES_TAR" \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/logs/*.log' \
    storage/app 2>/dev/null \
    public/uploads 2>/dev/null || true

if [[ -f "$FILES_TAR" ]]; then
    FILES_SIZE=$(du -h "$FILES_TAR" | cut -f1)
    echo "✅ Fichiers : $FILES_TAR ($FILES_SIZE)"
else
    echo "⚠️  Aucun dossier d'upload trouvé — backup DB uniquement"
fi

# ── ROTATION : garder les 14 derniers backups DB, 7 derniers fichiers ─
echo ""
echo "→ Rotation..."
ls -1t "$BACKUP_DIR"/optimize-db-*.dump 2>/dev/null | tail -n +15 | xargs -r rm -f
ls -1t "$BACKUP_DIR"/optimize-db-*.sql.gz 2>/dev/null | tail -n +15 | xargs -r rm -f
ls -1t "$BACKUP_DIR"/optimize-storage-*.tar.gz 2>/dev/null | tail -n +8 | xargs -r rm -f

echo ""
echo "═══════════════════════════════════════════════"
echo "✅ Backup terminé — $STAMP"
echo "═══════════════════════════════════════════════"
echo "Fichiers conservés :"
ls -lh "$BACKUP_DIR"/optimize-* 2>/dev/null | tail -6
