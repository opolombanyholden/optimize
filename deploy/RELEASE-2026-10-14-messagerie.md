# Mise en production — version « Messagerie » (14/10/2026)

Serveur : **anpiconnect.ga** — LWS cPanel, application à la racine `/home/rs2755624/public_html/`.
Procédure générale : [`UPDATE-lws-cpanel.md`](UPDATE-lws-cpanel.md). Ce document liste ce qui est **propre à cette version**.

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

Toutes les commandes serveur se lancent en SSH depuis `~/public_html`.

### 1. Sauvegarde (obligatoire)
```bash
cd ~/public_html
bash deploy/scripts/backup.sh          # dump PostgreSQL + fichiers uploadés dans ~/backups/
ls -lh ~/backups | tail -3             # noter le nom du .dump créé
```

### 2. Récupérer le code puis pré-vérifier
**Voie A — cPanel Git (recommandée)** : *Git Version Control* → dépôt `optimize` → *Pull or Deploy* →
**Update from Remote**, puis lancer la pré-vérification **avant** de cliquer *Deploy HEAD Commit* :
```bash
cd ~/repositories/optimize 2>/dev/null || cd ~/public_html   # selon l'emplacement du clone cPanel
bash deploy/scripts/precheck-mails.sh
```
Si tout est ✅ : cliquer **Deploy HEAD Commit**. Le `.cpanel.yml` enchaîne `composer install`,
`migrate --force`, `storage:link` et la reconstruction des caches.

**Voie B — SSH, mise à jour contrôlée**
```bash
cd ~/public_html
git fetch origin && git status          # aucune modification locale attendue
git pull origin main
bash deploy/scripts/precheck-mails.sh   # doit finir par « Serveur prêt »
bash deploy/scripts/update.sh           # down → backup → composer → migrate → caches → up
```

Équivalent manuel de `update.sh` si besoin :
```bash
php artisan down --retry=60
COMPOSER_PROCESS_TIMEOUT=900 /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader --no-interaction
/usr/local/bin/ea-php83 artisan migrate --force
/usr/local/bin/ea-php83 artisan storage:link 2>/dev/null || true
/usr/local/bin/ea-php83 artisan optimize:clear
/usr/local/bin/ea-php83 artisan config:cache && /usr/local/bin/ea-php83 artisan route:cache
/usr/local/bin/ea-php83 artisan view:cache && /usr/local/bin/ea-php83 artisan event:cache
chmod -R 775 storage bootstrap/cache
/usr/local/bin/ea-php83 artisan up
```

### 3. Contrôles techniques
```bash
cd ~/public_html
/usr/local/bin/ea-php83 artisan migrate:status | tail -8     # les 6 migrations 2026_10_1x : Ran
bash deploy/scripts/healthcheck.sh                           # inclut webklex/php-imap
curl -sI https://anpiconnect.ga/login | head -1              # HTTP/2 200
tail -50 storage/logs/laravel.log                            # aucune nouvelle erreur
```

### 4. Recette fonctionnelle (navigation privée)
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
/usr/local/bin/ea-php83 artisan migrate:rollback --step=6 --force    # annule les 6 migrations de cette version
git checkout fa7745c                                                 # version précédente
COMPOSER_PROCESS_TIMEOUT=900 /opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader --no-interaction
/usr/local/bin/ea-php83 artisan optimize:clear
/usr/local/bin/ea-php83 artisan config:cache && /usr/local/bin/ea-php83 artisan route:cache && /usr/local/bin/ea-php83 artisan view:cache
php artisan up
```
En cas de problème de données, restaurer la sauvegarde de l'étape 1 :
`bash deploy/scripts/rollback.sh ~/backups/<fichier>.dump fa7745c`

---

## À savoir
- **Gmail / Microsoft 365** demandent un **mot de passe d'application** (et, pour Microsoft 365, l'accès IMAP/SMTP autorisé par l'administrateur). La page *Mon compte* l'indique.
- Ports acceptés dans les comptes mail : IMAP 993/143, SMTP 465/587/25/2525 (protection contre le sondage du réseau interne).
- Les pièces jointes en cours de rédaction sont stockées dans `storage/app/private/mail-pieces/` et purgées après 24 h.
- Les mails ne sont pas copiés en base : seuls le classement, les liens et l'historique CRM le sont.
