<?php

namespace App\Services\Mail;

use App\Models\Intranet\MailCompte;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Envoi SMTP depuis un compte mail professionnel (Symfony Mailer).
 * ssl_sortant = true → TLS implicite (465) ; false → STARTTLS si proposé (587).
 */
class EnvoiMail
{
    public function __construct(private MailCompte $compte) {}

    protected function transport(): EsmtpTransport
    {
        $t = new EsmtpTransport($this->compte->serveur_sortant, (int) $this->compte->port_sortant, (bool) $this->compte->ssl_sortant);
        $t->setUsername($this->compte->identifiant);
        $t->setPassword((string) $this->compte->mot_de_passe);
        return $t;
    }

    /** Vérifie la connexion + l'authentification SMTP. */
    public function tester(): void
    {
        try {
            $t = $this->transport();
            $t->start();
            $t->stop();
        } catch (TransportExceptionInterface $e) {
            throw new MailException(self::messageErreur($e->getMessage()), 0, $e);
        }
    }

    /**
     * Envoie un mail et retourne le MIME brut envoyé (pour copie dans « Envoyés »).
     *
     * @param array{a:array, cc?:array, cci?:array, sujet:string, html:string,
     *              en_reponse_a?:?string, references?:?string, priorite?:?string, accuse_lecture?:bool} $data
     * @param array<int, array{nom:string, mime:string, data:string}> $pieces
     */
    public function envoyer(array $data, array $pieces = []): string
    {
        $email = (new Email())
            ->from(new Address($this->compte->email, $this->compte->expediteur))
            ->to(...$data['a'])
            ->subject($data['sujet'])
            ->text(self::texteDepuisHtml($data['html']));
        // Images data: (logo de signature, images citées) → images intégrées cid:,
        // car Gmail et Outlook bloquent les images data: dans les mails reçus
        $email->html(self::integrerImages($email, $data['html']));

        // Priorité transmise au destinataire (X-Priority / Importance) ; « normale » = pas d'en-tête
        $priorites = ['urgente' => Email::PRIORITY_HIGHEST, 'haute' => Email::PRIORITY_HIGH, 'basse' => Email::PRIORITY_LOW];
        if (isset($priorites[$data['priorite'] ?? ''])) {
            $email->priority($priorites[$data['priorite']]);
            $email->getHeaders()->addTextHeader('Importance', $data['priorite'] === 'basse' ? 'low' : 'high');
        }
        // Accusé de lecture (le destinataire reste libre de l'envoyer)
        if (!empty($data['accuse_lecture'])) {
            $email->getHeaders()->addMailboxHeader('Disposition-Notification-To', new Address($this->compte->email, $this->compte->expediteur));
        }

        if (!empty($data['cc']))  $email->cc(...$data['cc']);
        if (!empty($data['cci'])) $email->bcc(...$data['cci']);

        // Fil de discussion (réponse)
        if (!empty($data['en_reponse_a'])) {
            $id = trim($data['en_reponse_a'], '<> ');
            $email->getHeaders()->addIdHeader('In-Reply-To', $id);
            $refs = preg_split('/\s+/', trim(($data['references'] ?? '').' <'.$id.'>'));
            $email->getHeaders()->addIdHeader('References', array_values(array_filter(array_map(fn($r) => trim($r, '<> '), $refs))));
        }

        foreach ($pieces as $p) {
            $email->attach($p['data'], $p['nom'], $p['mime']);
        }

        try {
            $envoye = $this->transport()->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new MailException(self::messageErreur($e->getMessage()), 0, $e);
        }
        return $envoye?->toString() ?? $email->toString();
    }

    /** Remplace chaque image data:image/…;base64 par une pièce intégrée référencée en cid:. */
    public static function integrerImages(Email $email, string $html): string
    {
        $vues = [];
        return preg_replace_callback('#(<img\b[^>]*?\bsrc=)(["\'])data:image/(png|jpe?g|gif|webp);base64,([A-Za-z0-9+/=\s]+)\2#i', function ($m) use ($email, &$vues) {
            $donnees = base64_decode(preg_replace('/\s+/', '', $m[4]), true);
            if ($donnees === false || $donnees === '') return $m[0];
            $hash = substr(sha1($donnees), 0, 16);
            $ext = strtolower($m[3]) === 'jpg' ? 'jpeg' : strtolower($m[3]);
            $nom = "img-{$hash}.{$ext}";
            if (!isset($vues[$hash])) {               // même image utilisée plusieurs fois : intégrée une seule fois
                $email->embed($donnees, $nom, 'image/'.$ext);
                $vues[$hash] = true;
            }
            return $m[1].$m[2].'cid:'.$nom.$m[2];
        }, $html);
    }

    public static function texteDepuisHtml(string $html): string
    {
        $html = preg_replace(['#<br\s*/?>#i', '#</(p|div|li|h[1-6]|tr)>#i'], "\n", $html);
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    public static function messageErreur(string $erreur): string
    {
        $e = strtolower($erreur);
        return match (true) {
            str_contains($e, '535') || str_contains($e, 'authenticat') || str_contains($e, 'credentials')
                => "Authentification SMTP refusée : vérifiez l'identifiant et le mot de passe (Gmail / Outlook : mot de passe d'application).",
            str_contains($e, 'certificate') || str_contains($e, 'ssl')
                => "Échec de la négociation SSL/TLS avec le serveur SMTP : vérifiez le port et le chiffrement.",
            str_contains($e, 'connection') || str_contains($e, 'timed out') || str_contains($e, 'resolve')
                => "Serveur SMTP injoignable : vérifiez l'adresse du serveur, le port et le chiffrement.",
            default => 'Erreur SMTP : '.$erreur,
        };
    }
}
