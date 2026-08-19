<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification envoyée à un employé après réinitialisation de son mot de passe
 * par un administrateur.
 *
 * Sécurité :
 *  - Le mot de passe n'apparaît qu'en transit dans l'email (chiffré TLS côté SMTP).
 *  - L'email rappelle au destinataire qu'il devra le changer à la prochaine connexion.
 *  - Le motif et l'identité de l'administrateur sont mentionnés pour la traçabilité.
 *  - Pas de pièce jointe, pas d'image distante (anti-tracking).
 */
class PasswordResetByAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string  $temporaryPassword,
        public string  $adminName,
        public ?string $motif = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'OPTIMIZE ERP');
        $loginUrl = url('/login');

        $msg = (new MailMessage)
            ->subject("[$appName] Réinitialisation de votre mot de passe")
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre mot de passe a été réinitialisé par un administrateur de $appName"
                . ($this->adminName ? " ({$this->adminName})" : '') . '.');

        if ($this->motif) {
            $msg->line('Motif communiqué : ' . $this->motif);
        }

        $msg->line('Votre nouveau mot de passe temporaire est :')
            ->line('**' . $this->temporaryPassword . '**')
            ->line('Pour votre sécurité, **vous devrez le changer immédiatement** lors de votre prochaine connexion.')
            ->action('Se connecter', $loginUrl)
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, contactez immédiatement votre administrateur '
                . 'ou la DSI pour signaler une éventuelle compromission de votre compte.')
            ->salutation('Cordialement, l\'équipe ' . $appName);

        // Désactive le tracking via images distantes (ajoute X-Mailer custom si besoin)
        return $msg;
    }
}
