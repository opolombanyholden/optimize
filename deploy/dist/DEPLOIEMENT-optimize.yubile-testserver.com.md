# Déploiement OptimiZe → `optimize.yubile-testserver.com`

Procédure pas-à-pas cPanel. Compter ~30 min si tout se passe bien.

## Fichiers à uploader

Depuis ce dossier `deploy/dist/`, préparer sur votre poste :
- `optimiz-latest.tar.gz` (20 Mo — code de l'app + vendor + assets buildés)
- `optimiz-latest.tar.gz.sha256`
- `install-from-archive.sh`
- `env.optimize-yubile-testserver.example`

---

## ÉTAPE 1 — Créer le sous-domaine dans cPanel

1. Se connecter à cPanel du compte hébergeur `yubile-testserver.com`
2. **Domains → Create A New Domain**
   - Domain : `optimize.yubile-testserver.com`
   - Document Root : **décocher** *« Share document root »* et saisir :
     ```
     /home/CPANELUSER/optimiz/public
     ```
     (remplacer `CPANELUSER` par le login cPanel réel)
3. **Submit**

Si l'hébergeur n'autorise pas un document root personnalisé, cocher *Share document root* et on utilisera un `.htaccess` de redirection (voir Étape 6 alternative).

---

## ÉTAPE 2 — Créer la base PostgreSQL

1. **cPanel → PostgreSQL Databases**
2. **Create New Database** :
   - Name : `optimiz` → sera préfixé automatiquement en `CPANELUSER_optimiz`
3. **Create New User** :
   - Username : `optprod` → devient `CPANELUSER_optprod`
   - Password : générer un mot de passe fort (32 caractères) — **noter ce mot de passe**
4. **Add User to Database** :
   - Sélectionner user + database → **Add**
   - Privileges : cocher **ALL PRIVILEGES**

> ⚠️ Si PostgreSQL indisponible : demander à l'hébergeur avant tout. La bascule MySQL exige des adaptations de code (voir `deploy/cpanel/README.md` section MySQL).

---

## ÉTAPE 3 — Vérifier PHP 8.3

1. **cPanel → Select PHP Version** (ou *MultiPHP Manager*)
2. Sélectionner `optimize.yubile-testserver.com` → PHP **8.3**
3. Onglet **Extensions** : cocher (si pas déjà) `pdo_pgsql`, `mbstring`, `intl`, `gd`, `zip`, `curl`, `xml`, `bcmath`, `fileinfo`, `openssl`
4. Onglet **Options** :
   - `memory_limit` = 512M
   - `upload_max_filesize` = 50M
   - `post_max_size` = 55M
   - `max_execution_time` = 300

---

## ÉTAPE 4 — Uploader les fichiers

**Via File Manager cPanel** :
1. Naviguer à `/home/CPANELUSER/`
2. Créer un dossier `optimiz` (au même niveau que `public_html`, PAS dedans)
3. Créer un dossier temporaire `upload` à côté
4. Upload dans `upload/` :
   - `optimiz-latest.tar.gz`
   - `optimiz-latest.tar.gz.sha256`
   - `install-from-archive.sh`
   - `env.optimize-yubile-testserver.example`

**Alternative SSH plus rapide** (si SSH activé) :
```bash
scp deploy/dist/{optimiz-latest.tar.gz,optimiz-latest.tar.gz.sha256,install-from-archive.sh,env.optimize-yubile-testserver.example} \
    CPANELUSER@yubile-testserver.com:~/upload/
```

---

## ÉTAPE 5 — Installer l'application

**Terminal cPanel** (ou SSH) :
```bash
cd ~/upload
chmod +x install-from-archive.sh
bash install-from-archive.sh optimiz-latest.tar.gz ~/optimiz
```

Le script :
1. Vérifie le SHA-256 de l'archive
2. Extrait dans `~/optimiz/`
3. Détecte l'absence de `.env` → **s'arrête**

Configurer le `.env` :
```bash
cp env.optimize-yubile-testserver.example ~/optimiz/.env
nano ~/optimiz/.env
```
Renseigner dans `.env` :
- `DB_DATABASE=CPANELUSER_optimiz` (préfixe = votre login cPanel réel)
- `DB_USERNAME=CPANELUSER_optprod`
- `DB_PASSWORD=` (celui noté à l'étape 2)
- `MAIL_PASSWORD=` (voir étape 8 SMTP)

**Relancer le script** — il détectera `.env` présent et enchaînera :
```bash
bash install-from-archive.sh optimiz-latest.tar.gz ~/optimiz
```
Il génère `APP_KEY`, applique les permissions, `storage:link`, migrations, seeders (rôles/permissions), cache Laravel complet.

---

## ÉTAPE 6 — Vérifier le document root

Si Étape 1 a bien pointé vers `/home/CPANELUSER/optimiz/public` : passer à l'étape 7.

**Sinon** (document root partagé avec public_html) :
```bash
# Copier le .htaccess de redirection
cp ~/optimiz/deploy/cpanel/public_htaccess ~/public_html/.htaccess
# Adapter le chemin dans le fichier :
sed -i 's|../optimiz/public/|../optimiz/public/|g' ~/public_html/.htaccess
```

---

## ÉTAPE 7 — Activer HTTPS (Let's Encrypt via AutoSSL)

1. **cPanel → SSL/TLS Status**
2. Sélectionner `optimize.yubile-testserver.com`
3. Clic **Run AutoSSL**
4. Attendre 2-5 min qu'un certificat soit émis
5. **cPanel → Domains → Force HTTPS Redirect** : activer pour ce sous-domaine

Test : `curl -I https://optimize.yubile-testserver.com` doit retourner `200`.

---

## ÉTAPE 8 — Créer un compte SMTP no-reply

1. **cPanel → Email Accounts → Create**
   - Email : `no-reply@optimize.yubile-testserver.com`
   - Password : générer fort — noter
2. Reporter le mot de passe dans `~/optimiz/.env` :
   ```
   MAIL_PASSWORD=<mot_de_passe_généré>
   ```
3. Recharger le cache config :
   ```bash
   cd ~/optimiz && /usr/local/bin/ea-php83 artisan config:cache
   ```

---

## ÉTAPE 9 — Cron jobs (scheduler + backup)

**cPanel → Cron Jobs → Add New Cron Job** — ajouter les 3 lignes suivantes :

| Fréquence | Commande |
|---|---|
| `* * * * *` | `cd /home/CPANELUSER/optimiz && /usr/local/bin/ea-php83 artisan schedule:run >> /dev/null 2>&1` |
| `*/5 * * * *` | `cd /home/CPANELUSER/optimiz && /usr/local/bin/ea-php83 artisan queue:work --stop-when-empty --max-time=280 --tries=3 >> /dev/null 2>&1` |
| `0 2 * * *` | `mkdir -p /home/CPANELUSER/backups && pg_dump -h 127.0.0.1 -U CPANELUSER_optprod CPANELUSER_optimiz \| gzip > /home/CPANELUSER/backups/db-$(date +\%Y\%m\%d).sql.gz` |

Le worker queue toutes les 5 min est utile pour les notifications (sinon garder `QUEUE_CONNECTION=sync` dans `.env`).

---

## ÉTAPE 10 — Test de bonne santé

1. Ouvrir **https://optimize.yubile-testserver.com** → page de login OptimiZe visible ✓
2. Se connecter avec le compte super-admin créé par le seeder :
   - Email : `admin@optimize.local` (par défaut du seeder)
   - Password : `password` (par défaut — **à changer immédiatement**)
3. Vérifier `~/optimiz/storage/logs/laravel-YYYY-MM-DD.log` : aucune erreur
4. Créer un incident MG (test workflow)
5. Uploader une pièce jointe (test permissions `storage/app/public`)

---

## Mises à jour ultérieures

Générer une nouvelle archive en local puis :

```bash
# Sur votre poste
scp deploy/dist/optimiz-latest.tar.gz* deploy/dist/install-from-archive.sh \
    CPANELUSER@yubile-testserver.com:~/upload/

# Sur le serveur (SSH ou Terminal cPanel)
cd ~/upload
bash install-from-archive.sh optimiz-latest.tar.gz ~/optimiz
```

Le script détecte l'installation existante → mode UPDATE (backup `.env`, maintenance, migrations, cache, sortie de maintenance). Le `.env` est préservé.

---

## En cas de problème

**500 Internal Server Error** : lire `~/optimiz/storage/logs/laravel-*.log`. Souvent :
- `.env` mal formé → vérifier avec `php artisan config:cache`
- Permissions storage → `chmod -R 775 ~/optimiz/storage ~/optimiz/bootstrap/cache`

**Page blanche / 404** : vérifier le document root pointe bien vers `~/optimiz/public` (Étape 1).

**Connexion DB refusée** : vérifier user PostgreSQL bien attaché avec ALL PRIVILEGES (Étape 2 → *Add User to Database*).

**Certificat SSL non émis** : DNS pas propagé — attendre 15-30 min, relancer AutoSSL.

**Emails non reçus** : vérifier port 465 ouvert sortant côté hébergeur, ou basculer sur port 587 avec `MAIL_ENCRYPTION=tls`.

---

Contact technique : Yubile Technologie.
