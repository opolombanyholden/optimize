<?php

namespace App\Services\Mail;

use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\CrmInteraction;
use App\Models\Intranet\MailCompte;
use App\Models\Intranet\Opportunite;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Pont webmail ↔ CRM : retrouve les contacts / organisations correspondant aux adresses
 * d'un mail et journalise le mail dans l'historique CRM (CrmInteraction de type « email »).
 */
class CrmMail
{
    /** Cibles journalisables : clé « type:id » → [modèle, permission requise]. */
    public const TYPES = [
        'contact'      => [Contact::class, 'update:contact'],
        'organisation' => [ContactOrganisation::class, 'update:organisation_crm'],
        'opportunite'  => [Opportunite::class, 'update:opportunite'],
    ];

    /**
     * Contacts et organisations correspondant aux adresses données (insensible à la casse),
     * avec les opportunités ouvertes liées.
     *
     * @return array{contacts: \Illuminate\Support\Collection, organisations: \Illuminate\Support\Collection, opportunites: \Illuminate\Support\Collection}
     */
    public static function correspondants(array $emails): array
    {
        $emails = array_values(array_unique(array_filter(array_map(fn($e) => strtolower(trim((string) $e)), $emails), fn($e) => str_contains($e, '@'))));
        if (!$emails) return ['contacts' => collect(), 'organisations' => collect(), 'opportunites' => collect()];

        $contacts = Contact::with('organisation:id,nom')
            ->whereIn(DB::raw('lower(email)'), $emails)
            ->orderBy('nom')->get(['id', 'nom', 'prenoms', 'email', 'poste', 'organisation_id']);

        $orgIds = $contacts->pluck('organisation_id')->filter();
        $organisations = ContactOrganisation::query()
            ->where(fn($q) => $q->whereIn(DB::raw('lower(email)'), $emails)
                ->orWhereIn(DB::raw('lower(contact_principal_email)'), $emails)
                ->orWhereIn('id', $orgIds))
            ->orderBy('nom')->get(['id', 'nom', 'email', 'type']);

        $opportunites = Opportunite::with('etape:id,nom,couleur')
            ->where('statut', 'ouvert')
            ->where(fn($q) => $q->whereIn('contact_id', $contacts->pluck('id'))->orWhereIn('organisation_id', $organisations->pluck('id')))
            ->orderByDesc('updated_at')->limit(20)
            ->get(['id', 'titre', 'reference', 'contact_id', 'organisation_id', 'etape_id', 'valeur', 'devise']);

        return compact('contacts', 'organisations', 'opportunites');
    }

    /** Résout une clé « type:id » en modèle, si l'utilisateur a le droit de l'alimenter. */
    public static function cible(string $cle, User $user): ?Model
    {
        if (!preg_match('/^(contact|organisation|opportunite):(\d+)$/', $cle, $m)) return null;
        [$classe, $permission] = self::TYPES[$m[1]];
        if (!$user->can($permission)) return null;
        return $classe::find((int) $m[2]);
    }

    /**
     * Ajoute le mail à l'historique de la cible. Retourne false si ce mail y figure déjà.
     *
     * @param array{sujet:string, texte:?string, html:?string, date:?\DateTimeInterface, message_id:?string} $mail
     */
    public static function journaliser(Model $cible, array $mail, string $sens, MailCompte $compte, User $user, ?int $uid = null, ?string $dossier = null): bool
    {
        $messageId = trim((string) ($mail['message_id'] ?? '')) ?: null;
        if ($messageId && $cible->interactions()->where('mail_message_id', $messageId)->exists()) {
            return false;
        }

        $date = $mail['date'] ?? now();
        $prefixe = $sens === 'envoye' ? 'Mail envoyé' : 'Mail reçu';
        $cible->interactions()->create([
            'type'             => 'email',
            'objet'            => mb_substr($prefixe.' : '.$mail['sujet'], 0, 255),
            'description'      => self::extrait($mail['texte'] ?? null, $mail['html'] ?? null),
            'date_interaction' => $date,
            'est_terminee'     => true,
            'realisee_par'     => $user->id,
            'mail_message_id'  => $messageId,
            'mail_compte_id'   => $compte->id,
            'mail_uid'         => $uid,
            'mail_dossier'     => $dossier,
            'mail_sens'        => $sens,
        ]);

        if ($cible instanceof Contact && (!$cible->derniere_interaction_le || $cible->derniere_interaction_le < $date)) {
            $cible->forceFill(['derniere_interaction_le' => $date])->saveQuietly();
        }
        return true;
    }

    /** Début du texte du mail, sans la citation du message précédent. */
    public static function extrait(?string $texte, ?string $html, int $max = 600): ?string
    {
        if (($texte === null || trim($texte) === '') && $html !== null) {
            $html = preg_replace('#<blockquote\b.*?</blockquote>#is', '', $html);
            $texte = EnvoiMail::texteDepuisHtml($html);
        }
        if ($texte === null) return null;

        $lignes = [];
        foreach (preg_split('/\R/', $texte) as $l) {
            if (str_starts_with(ltrim($l), '>')) continue;                            // lignes citées
            if (preg_match('/^(Le .+ a écrit|On .+ wrote)\s*:?\s*$/u', trim($l))) break; // en-tête de citation
            if (trim($l) === '--') break;                                           // séparateur de signature « -- »
            $lignes[] = $l;
        }
        $t = trim(preg_replace('/[ \t]+/', ' ', preg_replace('/\n{3,}/', "\n\n", implode("\n", $lignes))));
        return $t === '' ? null : (mb_strlen($t) > $max ? mb_substr($t, 0, $max).'…' : $t);
    }

    /** Message-ID extrait d'un MIME brut (mail envoyé). */
    public static function messageIdDepuisMime(string $mime): ?string
    {
        return preg_match('/^Message-ID:\s*(<[^>]+>)/mi', $mime, $m) ? $m[1] : null;
    }
}
