# Mise en production — version « Messagerie » (14/10/2026)

Serveur : **anpiconnect.ga** — LWS `web43.lws-hosting.com`, compte cPanel `anpiconnect`, application à la racine de `~/public_html`
(PostgreSQL `anpiconnect_optimize`, PHP 8.3, composer 2.9 disponible).
⚠️ Ne pas confondre avec `web39` (même nom de compte, autre application : `portail.anpiconnect.net`).

**Mode de déploiement : zip uploadé via cPanel + commandes dans le terminal cPanel.**

Version en production avant cette mise à jour : `db991fe`–`fa7745c` (vérifié par empreintes, aucune modification locale), migrations jusqu'à `2026_09_07_100000_create_systeme_config`.

Archive : `deploy/dist/optimize-20261014-maj.zip` (332 Ko, non versionnée) — **différentielle** : uniquement les 84 fichiers modifiés depuis `db991fe` (aucun fichier supprimé ni renommé). `vendor/` est reconstruit sur le serveur par `composer install`.

---

## Ce qui change

| Domaine | Changements |
|---|---|
| **Mails pro** | Webmail IMAP/SMTP (lecture, rédaction, réponse, transfert, pièces jointes avec aperçu et quotas), plusieurs comptes mail par utilisateur (« Mon compte »), dossier « À traiter » |
| **Classement des mails** | Lien d'un mail vers projet, tâche, courrier, opportunité, dépense, facture…, priorité, catégorie, échéance, note interne ; bloc « Mails liés » sur les fiches |
| **Signatures** | Plusieurs signatures par utilisateur, éditeur visuel ou HTML/CSS, logo redimensionnable (envoyé en image intégrée), **modèles d'entreprise** applicables à tous (Administration → Modèles de signature) |
| **CRM** | Mails journalisés dans l'historique des contacts / organisations / opportunités, autocomplétion des destinataires |
| **Messagerie instantanée** | Conversations privées 1-à-1 réelles (remplace le widget factice), présence en ligne |
| **Discussions de groupe** | Refonte complète (mobile d'abord), non-lus, derniers messages ; correctif : affichage des 200 messages **les plus récents** |
| **Administration** | Import / export des utilisateurs (CSV, XLSX) ; référentiels RH : nouvelle présentation + import / export |
| **Navigation** | Menu Intranet réorganisé (Mail pro en tête), référentiels RH déplacés dans le module RH, réseau social intégré à l'Intranet |
| **Correctifs** | Recherche du partage social (commandes, devis, livraisons, demandes, absences, formations) |

### Nouvelle dépendance Composer
- `webklex/php-imap` ^6.2 (client IMAP en PHP pur : remplace `ext-imap`, qui n'envoie pas le SNI et échoue avec Gmail, Microsoft 365, OVH…)

### 6 nouvelles migrations
```
2026_10_13_100000_create_chat_tables                 (chat_conversations, chat_messages, users.last_seen_at)
2026_10_13_110000_add_etat_to_intranet_mail_comptes  (nom_expediteur, état de connexion, unique user+email)
2026_10_14_100000_create_mail_signatures
2026_10_14_110000_add_mail_to_crm_interactions
2026_10_14_120000_create_mail_signature_modeles
2026_10_14_130000_create_mail_suivis                 (mail_suivis, mail_suivi_liens)
```
Toutes sont **additives** (aucune suppression de données) et réversibles.

---

## Prérequis serveur (à vérifier une fois)

| Point | Exigence | Où le régler |
|---|---|---|
| Extensions PHP | `openssl json mbstring iconv libxml dom zip fileinfo pdo_pgsql` (plus besoin de `imap`) | cPanel → *Select PHP Version* → Extensions |
| Upload | `upload_max_filesize ≥ 20M`, `post_max_size ≥ 26M` | cPanel → *MultiPHP INI Editor* |
| Connexions sortantes | Ports **993, 143** (IMAP) et **465, 587** (SMTP) ouverts en sortie | Support LWS si bloqués |
| `APP_KEY` | Inchangé — **ne jamais le régénérer** : les mots de passe des comptes mail sont chiffrés avec | `.env` |

Le script `deploy/scripts/precheck-mails.sh` vérifie tout cela en lecture seule.

---

## Déploiement pas à pas

Toutes les commandes se lancent dans le **terminal cPanel** de `web43`.

### 1. Sauvegarde (obligatoire, avant toute chose)
```bash
cd ~/public_html
mkdir -p ~/backups && STAMP=$(date +%Y%m%d-%H%M%S) && echo "Horodatage : $STAMP"
env_val() { grep "^$1=" .env | cut -d= -f2- | tr -d '"'; }
PGPASSWORD="$(env_val DB_PASSWORD)" pg_dump -h "$(env_val DB_HOST)" -p "$(env_val DB_PORT)" \
  -U "$(env_val DB_USERNAME)" -d "$(env_val DB_DATABASE)" -F c -f ~/backups/optimize-db-$STAMP.dump
tar czf ~/backups/optimize-site-$STAMP.tar.gz --exclude=./.git --exclude=./storage/logs --exclude=./storage/framework .
ls -lh ~/backups/
```

### 2. Upload du zip
cPanel → **Gestionnaire de fichiers** → `public_html` → **Charger** `optimize-20261014-maj.zip` (ne pas l'extraire depuis le gestionnaire).
```bash
cd ~/public_html && sha256sum optimize-20261014-maj.zip   # doit correspondre à optimize-20261014-maj.zip.sha256
```

### 3. Mise à jour
```bash
cd ~/public_html
php artisan down --retry=60

unzip -o optimize-20261014-maj.zip -d ~/public_html
composer install --no-dev --optimize-autoloader --no-interaction   # ajoute webklex/php-imap

bash deploy/scripts/precheck-mails.sh          # doit finir par « Serveur prêt »

php artisan optimize:clear
php artisan migrate:status | grep -i pending   # 6 migrations 2026_10_1x attendues
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
chmod -R 775 storage bootstrap/cache

php artisan up
rm -f ~/public_html/optimize-20261014-maj.zip
```

### 3 bis. Correction de configuration recommandée
Le `.env` de production contient encore `APP_ENV=local` et `APP_URL=http://localhost`. Les liens absolus
(fichiers stockés, liens dans les mails, notifications) pointent alors vers `localhost`.
```bash
cd ~/public_html
cp .env ~/backups/env-$(date +%Y%m%d-%H%M%S)
sed -i 's#^APP_ENV=.*#APP_ENV=production#; s#^APP_URL=.*#APP_URL=https://anpiconnect.ga#' .env
grep -E '^(APP_ENV|APP_URL|APP_DEBUG)=' .env
php artisan config:cache
```

### 4. Contrôles techniques
```bash
cd ~/public_html
php artisan migrate:status | tail -8             # les 6 migrations 2026_10_1x : Ran
bash deploy/scripts/healthcheck.sh               # inclut webklex/php-imap
curl -sI https://anpiconnect.ga/login | head -1  # HTTP 200
tail -50 storage/logs/laravel.log                # aucune nouvelle erreur
```

### 5. Recette fonctionnelle (navigation privée)
- [ ] Menu Intranet : *Tableau de bord*, puis **Mail pro** mis en évidence, *Messagerie instantanée*, accordéons, section *Réseau social*
- [ ] **Mon compte** → *Connecter un compte* (ex. Gmail avec mot de passe d'application) → « Compte connecté »
- [ ] **Mail pro** : la boîte de réception s'affiche ; ouvrir un mail ; *Répondre* avec une pièce jointe (barre de progression + quota) ; le mail arrive
- [ ] Sur un mail : *Classer / lier* → lier à un projet → le mail apparaît dans « Mails liés » sur la fiche du projet
- [ ] **Mon compte → Signatures** : créer une signature avec logo ; l'envoyer ; le logo s'affiche chez le destinataire (Gmail)
- [ ] **Administration → Modèles de signature** : créer un modèle, *Appliquer à tous*
- [ ] **Messagerie instantanée** : écrire à un collègue, réponse reçue sans recharger la page (≈ 4 s)
- [ ] **Discussion de groupe** : liste, ouverture d'un groupe, envoi de message, affichage mobile
- [ ] **Administration → Utilisateurs** : *Exporter* (XLSX) ; **RH → Référentiels → Départements** : *Exporter* / *Importer*

---

## Retour arrière
```bash
cd ~/public_html
php artisan down
php artisan migrate:rollback --step=6 --force          # AVANT de remettre l'ancien code (les fichiers de migration doivent exister)
STAMP=XXXXXXXX-XXXXXX                                    # horodatage de la sauvegarde de l'étape 1
tar xzf ~/backups/optimize-site-$STAMP.tar.gz -C ~/public_html
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
Restauration complète de la base si nécessaire :
```bash
env_val() { grep "^$1=" .env | cut -d= -f2- | tr -d '"'; }
PGPASSWORD="$(env_val DB_PASSWORD)" pg_restore -h "$(env_val DB_HOST)" -p "$(env_val DB_PORT)" \
  -U "$(env_val DB_USERNAME)" -d "$(env_val DB_DATABASE)" --clean --if-exists ~/backups/optimize-db-$STAMP.dump
```

---

## À savoir
- **Gmail / Microsoft 365** demandent un **mot de passe d'application** (et, pour Microsoft 365, l'accès IMAP/SMTP autorisé par l'administrateur). La page *Mon compte* l'indique.
- Ports acceptés dans les comptes mail : IMAP 993/143, SMTP 465/587/25/2525 (protection contre le sondage du réseau interne).
- Sur `web43`, le port sortant **143 est fermé** : les comptes IMAP doivent utiliser **993 (SSL)** — c'est le cas de Gmail, Microsoft 365, OVH, Zoho, Yahoo.
- Aucun cron Laravel n'est configuré ; cette version n'en a pas besoin.
- Les pièces jointes en cours de rédaction sont stockées dans `storage/app/private/mail-pieces/` et purgées après 24 h.
- Les mails ne sont pas copiés en base : seuls le classement, les liens et l'historique CRM le sont.
