#!/usr/bin/env bash
# ═════════════════════════════════════════════════════════════
# OptimiZe ERP — Bootstrap serveur (Debian/Ubuntu)
# ═════════════════════════════════════════════════════════════
# Script à exécuter UNE SEULE FOIS sur un serveur vierge en root
# pour installer toutes les dépendances système + créer la DB
# + configurer Nginx, PHP-FPM, Supervisor, Cron.
#
# Usage :
#   sudo bash install-server.sh <domaine> <db_password>
#
# Exemple :
#   sudo bash install-server.sh erp.example.com "MonMotDePasseFort123!"
# ─────────────────────────────────────────────────────────────
set -euo pipefail

DOMAIN="${1:?Usage: install-server.sh <domaine> <db_password>}"
DB_PASS="${2:?Usage: install-server.sh <domaine> <db_password>}"
APP_ROOT="/var/www/optimiz"
DB_NAME="optimiz_prod"
DB_USER="optimiz_prod"

if [ "$EUID" -ne 0 ]; then
    echo "✗ Ce script doit être lancé en root (sudo)"
    exit 1
fi

echo "═══════════════════════════════════════════════════"
echo "  OptimiZe — Install serveur pour $DOMAIN"
echo "═══════════════════════════════════════════════════"

# 1) Mise à jour système
echo "→ apt update && upgrade"
apt update -y
DEBIAN_FRONTEND=noninteractive apt upgrade -y

# 2) Dépôts PHP 8.3 + PostgreSQL 17
echo "→ Ajout dépôts (PHP Sury + PostgreSQL PGDG)"
apt install -y ca-certificates apt-transport-https lsb-release gnupg curl wget

if [ ! -f /etc/apt/sources.list.d/php.list ]; then
    curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb
    dpkg -i /tmp/debsuryorg-archive-keyring.deb
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
fi
if [ ! -f /etc/apt/sources.list.d/pgdg.list ]; then
    curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /usr/share/keyrings/postgresql.gpg
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/postgresql.gpg] http://apt.postgresql.org/pub/repos/apt $(lsb_release -sc)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
fi
apt update -y

# 3) Paquets requis
echo "→ Installation PHP 8.3 + extensions + PostgreSQL 17 + Nginx + Supervisor"
DEBIAN_FRONTEND=noninteractive apt install -y \
    php8.3 php8.3-fpm php8.3-cli php8.3-common \
    php8.3-pgsql php8.3-mbstring php8.3-intl \
    php8.3-gd php8.3-zip php8.3-curl php8.3-xml php8.3-bcmath \
    postgresql-17 postgresql-client-17 \
    nginx supervisor \
    git unzip zip \
    certbot python3-certbot-nginx \
    ufw fail2ban

# 4) Composer
if ! command -v composer >/dev/null 2>&1; then
    echo "→ Installation Composer"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
fi

# 5) Node.js 20 (optionnel — pour rebuild frontend)
if ! command -v node >/dev/null 2>&1; then
    echo "→ Installation Node.js 20"
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
fi

# 6) Base de données PostgreSQL
echo "→ Création base $DB_NAME"
sudo -u postgres psql <<EOF
DO \$\$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = '$DB_USER') THEN
        CREATE ROLE $DB_USER WITH LOGIN PASSWORD '$DB_PASS';
    ELSE
        ALTER ROLE $DB_USER WITH PASSWORD '$DB_PASS';
    END IF;
END
\$\$;
SELECT 'CREATE DATABASE $DB_NAME OWNER $DB_USER ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = '$DB_NAME')\\gexec
GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;
EOF

# 7) Répertoires applicatif + backups
echo "→ Préparation répertoires"
mkdir -p "$APP_ROOT" /var/backups/optimiz /var/log/supervisor
chown -R www-data:www-data "$APP_ROOT" /var/backups/optimiz

# 8) UFW (firewall)
echo "→ Configuration firewall (SSH + HTTP + HTTPS)"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# 9) PHP-FPM ajustements upload
echo "→ Ajustement PHP-FPM (upload 50Mo)"
FPMINI="/etc/php/8.3/fpm/php.ini"
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 50M/' "$FPMINI"
sed -i 's/^post_max_size.*/post_max_size = 55M/' "$FPMINI"
sed -i 's/^memory_limit.*/memory_limit = 512M/' "$FPMINI"
sed -i 's/^max_execution_time.*/max_execution_time = 300/' "$FPMINI"

# 10) Services actifs
echo "→ Enable + restart services"
systemctl enable --now php8.3-fpm nginx postgresql supervisor
systemctl restart php8.3-fpm

# 11) Nginx virtual host (temporaire HTTP avant SSL)
echo "→ Config Nginx temporaire (HTTP) pour Certbot"
cat > /etc/nginx/sites-available/optimiz.conf <<NGINX
server {
    listen 80;
    server_name $DOMAIN;
    root $APP_ROOT/public;
    index index.php;

    client_max_body_size 50M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 300;
    }
    location ~ /\.env { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/optimiz.conf /etc/nginx/sites-enabled/optimiz.conf
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo ""
echo "✓ Serveur prêt."
echo ""
echo "PROCHAINES ÉTAPES :"
echo "  1. Pousser le code de l'app dans $APP_ROOT"
echo "     Option A : sur votre poste dev → bash deploy/push-deploy.sh $DOMAIN"
echo "     Option B : sur le serveur     → git clone <URL> $APP_ROOT"
echo ""
echo "  2. Configurer .env :"
echo "     cd $APP_ROOT && cp deploy/.env.production.example .env"
echo "     nano .env  # renseigner APP_URL=https://$DOMAIN, DB_PASSWORD=$DB_PASS, mail…"
echo "     php artisan key:generate --force"
echo ""
echo "  3. Migrations + seeds initiaux :"
echo "     php artisan migrate --force"
echo "     php artisan db:seed --class=RolePermissionSeeder --force"
echo ""
echo "  4. Certificat HTTPS :"
echo "     sudo certbot --nginx -d $DOMAIN --agree-tos --no-eff-email -m admin@$DOMAIN"
echo "     puis remplacer /etc/nginx/sites-available/optimiz.conf par deploy/nginx.conf.example"
echo ""
echo "  5. Supervisor + cron :"
echo "     sudo cp $APP_ROOT/deploy/supervisor.conf.example /etc/supervisor/conf.d/optimiz-worker.conf"
echo "     sudo supervisorctl reread && sudo supervisorctl update"
echo "     sudo crontab -u www-data -e   # coller deploy/crontab.example"
