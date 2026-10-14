# Mise en production — version « Messagerie » (14/10/2026)

Serveur : **anpiconnect.ga** — LWS cPanel, utilisateur `anpiconnect`, application à la racine de `~/public_html`.
**Mode de déploiement : zip uploadé via cPanel + commandes dans le terminal cPanel** (pas de git sur le serveur).

Archive : `deploy/dist/optimize-20261014-messagerie.zip` (construite localement, non versionnée) —
contient le code + `vendor/` de production ; **ne contient pas** `.env`, les fichiers de `storage/`, `conception/`, `docs/`.

Version précédente en production : commit `fa7745c` (point de retour arrière).

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

Toutes les commandes se lancent dans le **terminal cPanel**.

### 1. Sauvegarde (obligatoire, avant toute chose)
```bash
cd ~/public_html
mkdir -p ~/backups && STAMP=$(date +%Y%m%d-%H%M%S)
env_val() { grep "^$1=" .env | cut -d= -f2- | tr -d '"'; }

# Base de données (PostgreSQL ; si DB_CONNECTION=mysql, utiliser la variante mysqldump)
echo "Base : $(env_val DB_CONNECTION) / $(env_val DB_DATABASE)"
PGPASSWORD="$(env_val DB_PASSWORD)" pg_dump -h "$(env_val DB_HOST)" -p "$(env_val DB_PORT)" \
  -U "$(env_val DB_USERNAME)" -d "$(env_val DB_DATABASE)" -F c -f ~/backups/optimize-db-$STAMP.dump
# mysqldump -h "$(env_val DB_HOST)" -u "$(env_val DB_USERNAME)" -p"$(env_val DB_PASSWORD)" "$(env_val DB_DATABASE)" > ~/backups/optimize-db-$STAMP.sql

# Code actuel complet (vendor, .env, fichiers déposés) pour pouvoir revenir en arrière
tar czf ~/backups/optimize-site-$STAMP.tar.gz --exclude=./storage/logs --exclude=./storage/framework .
ls -lh ~/backups/
```

### 2. Upload du zip
cPanel → **Gestionnaire de fichiers** → `public_html` → **Charger** `optimize-20261014-messagerie.zip`.
Ne pas extraire depuis le gestionnaire : l'extraction se fait au terminal (étape 3).

Contrôle d'intégrité (doit afficher la même empreinte que `optimize-20261014-messagerie.zip.sha256`) :
```bash
cd ~/public_html && sha256sum optimize-20261014-messagerie.zip
```

### 3. Mise à jour
```bash
cd ~/public_html
php -v | head -1                          # doit indiquer PHP 8.3
php artisan down --retry=60               # maintenance

unzip -oq optimize-20261014-messagerie.zip -d ~/public_html   # écrase le code, conserve .env et storage/
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/events.php

bash deploy/scripts/precheck-mails.sh     # extensions, upload, ports IMAP/SMTP — doit finir par « Serveur prêt »

php artisan optimize:clear
php artisan package:discover
php artisan migrate:status | grep -i pending     # les 6 migrations 2026_10_1x
php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
chmod -R 775 storage bootstrap/cache

php artisan up
rm -f ~/public_html/optimize-20261014-messagerie.zip   # ne pas laisser l'archive sur le serveur
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
php artisan migrate:rollback --step=6 --force          # annule les 6 migrations de cette version (avant de remettre l'ancien code)
STAMP=XXXXXXXX-XXXXXX                                    # horodatage de la sauvegarde de l'étape 1
tar xzf ~/backups/optimize-site-$STAMP.tar.gz -C ~/public_html
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
Si les données sont en cause, restaurer aussi la base :
```bash
env_val() { grep "^$1=" .env | cut -d= -f2- | tr -d '"'; }
PGPASSWORD="$(env_val DB_PASSWORD)" pg_restore -h "$(env_val DB_HOST)" -p "$(env_val DB_PORT)" \
  -U "$(env_val DB_USERNAME)" -d "$(env_val DB_DATABASE)" --clean --if-exists ~/backups/optimize-db-$STAMP.dump
```

---

## À savoir
- **Gmail / Microsoft 365** demandent un **mot de passe d'application** (et, pour Microsoft 365, l'accès IMAP/SMTP autorisé par l'administrateur). La page *Mon compte* l'indique.
- Ports acceptés dans les comptes mail : IMAP 993/143, SMTP 465/587/25/2525 (protection contre le sondage du réseau interne).
- Les pièces jointes en cours de rédaction sont stockées dans `storage/app/private/mail-pieces/` et purgées après 24 h.
- Les mails ne sont pas copiés en base : seuls le classement, les liens et l'historique CRM le sont.
