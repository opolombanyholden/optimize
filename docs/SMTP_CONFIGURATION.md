# Configuration SMTP — OptimiZ ERP

Ce document décrit comment configurer l'envoi d'emails pour les notifications du système (réinitialisation de mot de passe, notifications RH, etc.).

## État actuel

Le fichier `.env` contient des valeurs placeholder :
```
MAIL_HOST=smtp.example.com
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=
```
**Conséquence :** aucun email réel n'est envoyé. Le système bascule automatiquement sur le fallback OTP one-time (affichage à l'écran pour l'admin).

## Driver Laravel `log` pour le développement

En dev/local, le plus simple est de capturer les emails dans les logs :

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@optimize.local"
MAIL_FROM_NAME="${APP_NAME}"
```
Les emails seront écrits dans `storage/logs/laravel.log` (entiers, formatés comme s'ils étaient envoyés).

## Driver `array` pour les tests automatisés

Déjà configuré dans `phpunit.xml` :
```xml
<env name="MAIL_MAILER" value="array"/>
```
Permet d'inspecter les emails via `Mail::fake()` / `Notification::fake()` sans rien envoyer.

## Production — options recommandées

### Option A — Mailgun (recommandé pour démarrage rapide)

1. Créer un compte sur https://www.mailgun.com (10 000 emails/mois gratuits)
2. Vérifier le domaine `optimize.local` (ou votre domaine production) avec les DNS fournis
3. Récupérer la clé API et le domaine
4. `composer require symfony/mailgun-mailer symfony/http-client`
5. Configurer `.env` :
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.votredomaine.com
MAILGUN_SECRET=key-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
MAILGUN_ENDPOINT=api.eu.mailgun.net   # ou api.mailgun.net selon la région
MAIL_FROM_ADDRESS="noreply@votredomaine.com"
MAIL_FROM_NAME="OPTIMIZE ERP"
```

### Option B — Amazon SES (pour fort volume / Gabon AWS)

1. Activer SES dans la console AWS (zone `eu-west-3` Paris recommandée pour AF/Europe)
2. Vérifier le domaine + sortir du sandbox (demande de production access)
3. Créer un user IAM avec policy `AmazonSESFullAccess`
4. `composer require aws/aws-sdk-php`
5. `.env` :
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=AKIAxxxxxxxx
AWS_SECRET_ACCESS_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
AWS_DEFAULT_REGION=eu-west-3
MAIL_FROM_ADDRESS="noreply@votredomaine.com"
```

### Option C — SMTP générique (OVH, Office365, Gmail Workspace, hébergeur local)

```env
MAIL_MAILER=smtp
MAIL_HOST=ssl0.ovh.net          # exemple OVH ; Office365: smtp.office365.com ; Gmail: smtp.gmail.com
MAIL_PORT=465                    # 465=SSL, 587=STARTTLS
MAIL_ENCRYPTION=ssl              # ou tls (port 587)
MAIL_USERNAME=noreply@votredomaine.com
MAIL_PASSWORD=motdepasse_smtp    # ou mot de passe d'application pour Gmail/Office
MAIL_FROM_ADDRESS="noreply@votredomaine.com"
MAIL_FROM_NAME="OPTIMIZE ERP"
```

⚠️ **Gmail/Workspace :** activer la validation en deux étapes et générer un *App Password* dédié (16 caractères). Le mot de passe utilisateur normal ne fonctionne pas.

## Vérification après configuration

```bash
# Vider les caches
php artisan config:clear

# Tester l'envoi en tinker
php artisan tinker
> Mail::raw('Test envoi OptimiZ', fn($m) => $m->to('vous@example.com')->subject('Test'));

# Ou tester le flow réel via une réinitialisation de mot de passe
# (vous devriez recevoir l'email PasswordResetByAdminNotification)
```

## File d'attente (queue) pour les emails

Par défaut, les emails sont envoyés de manière synchrone. Pour de meilleures perfs :

```env
QUEUE_CONNECTION=database
```

Puis :
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work --tries=3
```

La classe `PasswordResetByAdminNotification` utilise déjà le trait `Queueable` — il suffit d'activer la queue.

## Logging des envois

Les notifications déclenchent un audit Spatie ActivityLog (`activity('password_reset')`) qui trace :
- `email_sent` (bool) : succès/échec de l'envoi
- `email_to` : destinataire
- causer / subject / IP / user agent

Pour consulter : `/rh/audit-log`.

## Sécurité — bonnes pratiques

1. **TLS obligatoire** : ne jamais utiliser `MAIL_ENCRYPTION=null` en production
2. **SPF + DKIM + DMARC** : configurer ces enregistrements DNS pour éviter le marquage en spam et l'usurpation
3. **Pas de mot de passe en clair dans Git** : `.env` est gitignored, utiliser un secret manager (Vault, AWS Secrets, Doppler) en prod
4. **Throttling** : la classe `Notification` peut être limitée via `Mail::throttle()` pour éviter les abus
5. **Rejet des bounces** : configurer un webhook bounce sur Mailgun/SES pour désactiver automatiquement les comptes aux adresses invalides

## Dépannage rapide

| Symptôme | Cause probable | Action |
|---|---|---|
| « Connection refused » | Mauvais host/port | Vérifier `MAIL_HOST`, `MAIL_PORT` |
| « Authentication failed » | Identifiants invalides | Régénérer le mot de passe SMTP / App Password |
| Email reçu marqué SPAM | DNS mal configuré | Ajouter SPF, DKIM, DMARC ; ne pas envoyer depuis un domaine non vérifié |
| « TLS/SSL handshake failure » | Mismatch encryption/port | port 465 = SSL, port 587 = TLS/STARTTLS |
| Email non reçu, pas d'erreur | Mode `log` actif | Vérifier `MAIL_MAILER` et `storage/logs/laravel.log` |

## Liens de référence

- Doc Laravel Mail : https://laravel.com/docs/12.x/mail
- Mailgun PHP : https://documentation.mailgun.com/en/latest/api-sending.html
- AWS SES : https://docs.aws.amazon.com/ses/
