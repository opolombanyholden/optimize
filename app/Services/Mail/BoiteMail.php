<?php

namespace App\Services\Mail;

use App\Models\Intranet\MailCompte;
use Illuminate\Support\Carbon;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;

/**
 * Accès IMAP à la boîte d'un compte mail professionnel (webklex/php-imap, client en PHP pur).
 *
 * N'utilise pas ext-imap : sa bibliothèque c-client (2007) n'envoie pas le SNI, si bien que
 * Gmail, Microsoft 365, OVH… renvoient un certificat par défaut invalide. Ici la connexion
 * passe par les flux PHP (SNI + vérification du certificat et du nom d'hôte).
 *
 * Les messages sont lus en direct sur le serveur (pas de copie locale) et identifiés
 * par leur UID IMAP dans un dossier.
 */
class BoiteMail
{
    /** Noms de dossiers usuels par rôle (comparaison insensible à la casse, sur le dernier segment). */
    private const ROLES = [
        'sent'   => ['sent', 'sent items', 'sent messages', 'sent mail', 'envoyés', 'éléments envoyés', 'messages envoyés'],
        'drafts' => ['drafts', 'brouillons'],
        'trash'  => ['trash', 'deleted items', 'deleted messages', 'corbeille', 'éléments supprimés', 'bin'],
        'spam'   => ['spam', 'junk', 'junk e-mail', 'junk email', 'courrier indésirable', 'indésirables'],
    ];

    private const LIBELLES = ['inbox' => 'Boîte de réception', 'sent' => 'Envoyés', 'drafts' => 'Brouillons', 'trash' => 'Corbeille', 'spam' => 'Indésirables'];
    private const ICONES   = ['inbox' => 'fa-inbox', 'sent' => 'fa-paper-plane', 'drafts' => 'fa-file-pen', 'trash' => 'fa-trash', 'spam' => 'fa-ban', 'autre' => 'fa-folder'];

    private ?Client $client = null;
    /** @var array<string, Folder>|null nom complet décodé → dossier */
    private ?array $dossiersImap = null;
    private ?array $dossiersCache = null;

    public function __construct(private MailCompte $compte) {}

    public function __destruct()
    {
        try {
            $this->client?->disconnect();
        } catch (\Throwable) {
        }
    }

    // ═══ Connexion ═══════════════════════════════════════════

    /** Paramètres de connexion webklex (surchargeable pour les tests). */
    protected function configCompte(): array
    {
        return [
            'host'          => $this->compte->serveur_entrant,
            'port'          => (int) $this->compte->port_entrant,
            'encryption'    => $this->compte->ssl_entrant ? 'ssl' : 'starttls',
            'validate_cert' => true,
            'username'      => $this->compte->identifiant,
            'password'      => (string) $this->compte->mot_de_passe,
            'protocol'      => 'imap',
            'timeout'       => 20,
        ];
    }

    protected function client(): Client
    {
        if ($this->client) return $this->client;

        $manager = new ClientManager([
            'options'  => ['fetch_order' => 'desc', 'soft_fail' => true],
            // iconv : ne dépend pas d'ext-imap (absente de PHP ≥ 8.4)
            'decoding' => ['options' => ['header' => 'iconv', 'message' => 'iconv', 'attachment' => 'iconv']],
        ]);
        $client = $manager->make($this->configCompte());

        try {
            $client->connect();
        } catch (\Throwable $e) {
            throw new MailException(self::messageErreur(self::chaine($e)), 0, $e);
        }
        return $this->client = $client;
    }

    /** Exécute une opération IMAP en convertissant les erreurs en MailException lisible. */
    private function imap(callable $fn): mixed
    {
        try {
            return $fn();
        } catch (MailException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new MailException(self::messageErreur(self::chaine($e)), 0, $e);
        }
    }

    /** Teste la connexion IMAP ; retourne le nombre de messages de la boîte de réception. */
    public function tester(): int
    {
        return $this->imap(fn() => (int) ($this->dossierImap('INBOX')->examine()['exists'] ?? 0));
    }

    public function nonLus(string $dossier = 'INBOX'): int
    {
        return $this->imap(fn() => (int) ($this->dossierImap($dossier)->status()['unseen'] ?? 0));
    }

    // ═══ Dossiers ═════════════════════════════════════════════

    /** @return array<string, Folder> */
    private function dossiersImap(): array
    {
        if ($this->dossiersImap !== null) return $this->dossiersImap;

        $res = [];
        foreach ($this->imap(fn() => $this->client()->getFolders(false)) as $f) {
            $res[$f->full_name] = $f;
        }
        return $this->dossiersImap = $res;
    }

    private function dossierImap(string $nom): Folder
    {
        $dossiers = $this->dossiersImap();
        $f = $dossiers[$nom] ?? null;
        // INBOX est insensible à la casse (RFC 3501)
        if (!$f && strcasecmp($nom, 'INBOX') === 0) {
            foreach ($dossiers as $cle => $d) if (strcasecmp($cle, 'INBOX') === 0) { $f = $d; break; }
        }
        if (!$f) throw new MailException("Dossier « {$nom} » introuvable sur le serveur.");
        return $f;
    }

    /** @return array<int, array{nom:string, libelle:string, role:string, icone:string}> */
    public function dossiers(): array
    {
        if ($this->dossiersCache !== null) return $this->dossiersCache;

        $dossiers = [];
        foreach ($this->dossiersImap() as $nom => $f) {
            if ($f->no_select) continue;
            $role = $this->role($nom, $f->delimiter ?: '/');
            $dossiers[] = [
                'nom'     => $nom,
                'libelle' => self::LIBELLES[$role] ?? $f->name,
                'role'    => $role,
                'icone'   => self::ICONES[$role] ?? self::ICONES['autre'],
            ];
        }

        // Boîte de réception, puis dossiers système, puis le reste par ordre alphabétique
        $ordre = ['inbox' => 0, 'drafts' => 1, 'sent' => 2, 'spam' => 3, 'trash' => 4, 'autre' => 5];
        usort($dossiers, fn($a, $b) => [$ordre[$a['role']], $a['nom']] <=> [$ordre[$b['role']], $b['nom']]);

        // Deux dossiers de même rôle (ex. « Junk » et « Spam ») : le second garde son nom réel
        $vus = [];
        foreach ($dossiers as &$d) {
            if (isset($vus[$d['libelle']])) {
                $parts = explode('/', str_replace('.', '/', $d['nom']));
                $d['libelle'] .= ' ('.end($parts).')';
            }
            $vus[$d['libelle']] = true;
        }
        unset($d);

        return $this->dossiersCache = $dossiers;
    }

    public function dossierParRole(string $role): ?string
    {
        foreach ($this->dossiers() as $d) {
            if ($d['role'] === $role) return $d['nom'];
        }
        return null;
    }

    private function role(string $nom, string $delimiteur): string
    {
        if (strcasecmp($nom, 'INBOX') === 0) return 'inbox';
        $parts = explode($delimiteur, $nom);
        $dernier = mb_strtolower(end($parts));
        foreach (self::ROLES as $role => $noms) {
            if (in_array($dernier, $noms, true)) return $role;
        }
        return 'autre';
    }

    // ═══ Liste ════════════════════════════════════════════════

    /**
     * Messages d'un dossier, du plus récent au plus ancien.
     * @return array{total:int, messages:array<int, array>}
     */
    public function lister(string $dossier, int $page = 1, int $parPage = 25, ?string $recherche = null): array
    {
        return $this->imap(function () use ($dossier, $page, $parPage, $recherche) {
            $folder = $this->dossierImap($dossier);

            $requete = $folder->query();
            $recherche = trim((string) $recherche);
            $recherche !== '' ? $requete->whereText($recherche) : $requete->whereAll();

            // UID croissants ≈ ordre d'arrivée → du plus récent au plus ancien
            $uids = $requete->search()->map(fn($u) => (int) $u)->sortDesc()->values()->all();
            $total = count($uids);
            $tranche = array_slice($uids, ($page - 1) * $parPage, $parPage);
            if (!$tranche) return ['total' => $total, 'messages' => []];

            $parUid = [];
            $msgs = $folder->query()->whereUidIn($tranche)->setFetchBody(false)->leaveUnread()->get();
            foreach ($msgs as $m) $parUid[(int) $m->getUid()] = $m;

            $messages = [];
            foreach ($tranche as $uid) {
                $m = $parUid[$uid] ?? null;
                if (!$m) continue;
                $type = strtolower((string) $m->getHeader()?->get('content_type'));
                $mid = trim((string) $m->getMessageId(), '<> ');
                $messages[] = [
                    'uid'     => $uid,
                    'message_id' => $mid !== '' ? '<'.$mid.'>' : null,
                    'sujet'   => trim((string) $m->getSubject()) ?: '(sans objet)',
                    'de'      => self::adresses($m->getFrom())[0] ?? ['nom' => '', 'email' => ''],
                    'a'       => self::adresses($m->getTo())[0] ?? ['nom' => '', 'email' => ''],
                    'date'    => self::date($m),
                    'vu'      => $m->hasFlag('seen'),
                    'suivi'   => $m->hasFlag('flagged'),
                    'repondu' => $m->hasFlag('answered'),
                    // Sans télécharger le corps : multipart/mixed ≈ présence de pièces jointes
                    'pieces'  => str_contains($type, 'multipart/mixed'),
                ];
            }
            return ['total' => $total, 'messages' => $messages];
        });
    }

    // ═══ Lecture ══════════════════════════════════════════════

    private function message(string $dossier, int $uid, bool $corps = true): Message
    {
        $m = $this->dossierImap($dossier)->query()->setFetchBody($corps)->leaveUnread()->getMessageByUid($uid);
        if (!$m || !$m->getUid()) throw new MailException('Message introuvable (il a peut-être été déplacé ou supprimé).');
        return $m;
    }

    /** Lit un message complet et le marque comme lu côté serveur. */
    public function lire(string $dossier, int $uid): array
    {
        return $this->imap(function () use ($dossier, $uid) {
            $m = $this->message($dossier, $uid);

            $html  = $m->hasHTMLBody() ? $m->getHTMLBody() : null;
            $texte = $m->hasTextBody() ? $m->getTextBody() : null;

            $pieces = [];
            foreach ($m->getAttachments() as $a) {
                $mime = strtolower(explode(';', (string) ($a->content_type ?: 'application/octet-stream'))[0]);
                // Image intégrée référencée par Content-ID → data URI (≤ 2 Mo), pas une pièce jointe
                if ($html !== null && $a->id && str_contains($html, 'cid:'.$a->id) && str_starts_with($mime, 'image/') && strlen($a->content) < 2_000_000) {
                    $html = str_ireplace('cid:'.$a->id, 'data:'.$mime.';base64,'.base64_encode($a->content), $html);
                    continue;
                }
                $pieces[] = [
                    'partie' => (string) $a->part_number,
                    'nom'    => $a->name ?: ($a->filename ?: 'piece-jointe-'.$a->part_number),
                    'mime'   => $mime,
                    'taille' => (int) ($a->size ?: strlen((string) $a->content)),
                ];
            }

            $m->setFlag('Seen');

            return [
                'uid'         => $uid,
                'dossier'     => $dossier,
                'sujet'       => trim((string) $m->getSubject()) ?: '(sans objet)',
                'de'          => self::adresses($m->getFrom()),
                'repondre_a'  => self::adresses($m->getReplyTo()),
                'a'           => self::adresses($m->getTo()),
                'cc'          => self::adresses($m->getCc()),
                'date'        => self::date($m),
                'message_id'  => ($id = trim((string) $m->getMessageId(), '<> ')) !== '' ? '<'.$id.'>' : '',
                'references'  => self::references($m) ?: null,
                'html'        => $html,
                'texte'       => $texte,
                'pieces'      => $pieces,
                'suivi'       => $m->hasFlag('flagged'),
            ];
        });
    }

    /** Contenu d'une pièce jointe : [nom, mime, données]. */
    public function piece(string $dossier, int $uid, string $partie): array
    {
        return $this->imap(function () use ($dossier, $uid, $partie) {
            foreach ($this->message($dossier, $uid)->getAttachments() as $a) {
                if ((string) $a->part_number === $partie) {
                    $mime = strtolower(explode(';', (string) ($a->content_type ?: 'application/octet-stream'))[0]);
                    return [$a->name ?: ($a->filename ?: 'piece-jointe'), $mime, (string) $a->content];
                }
            }
            throw new MailException('Pièce jointe introuvable.');
        });
    }

    // ═══ Actions ══════════════════════════════════════════════

    public function marquerLu(string $dossier, int $uid, bool $lu): void
    {
        $this->imap(function () use ($dossier, $uid, $lu) {
            $m = $this->message($dossier, $uid, false);
            $lu ? $m->setFlag('Seen') : $m->unsetFlag('Seen');
        });
    }

    public function marquerSuivi(string $dossier, int $uid, bool $suivi): void
    {
        $this->imap(function () use ($dossier, $uid, $suivi) {
            $m = $this->message($dossier, $uid, false);
            $suivi ? $m->setFlag('Flagged') : $m->unsetFlag('Flagged');
        });
    }

    /** Déplace vers la corbeille ; supprime définitivement si on est déjà dans la corbeille. */
    public function supprimer(string $dossier, int $uid): void
    {
        $this->imap(function () use ($dossier, $uid) {
            $m = $this->message($dossier, $uid, false);
            $corbeille = $this->dossierParRole('trash');

            if ($corbeille && $corbeille !== $dossier) {
                if (!$m->move($this->dossierImap($corbeille)->path, true, true)) {
                    throw new MailException('Déplacement vers la corbeille impossible.');
                }
            } else {
                $m->delete(true);
            }
        });
    }

    /** Copie un message envoyé (MIME brut) dans le dossier « Envoyés », s'il existe. */
    public function ajouterAuxEnvoyes(string $mime): bool
    {
        $envoyes = $this->dossierParRole('sent');
        if (!$envoyes) return false;
        try {
            $this->dossierImap($envoyes)->appendMessage($mime, ['\\Seen']);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    // ═══ Décodage ═════════════════════════════════════════════

    /** @return array<int, array{nom:string, email:string}> */
    private static function adresses($attribut): array
    {
        $res = [];
        foreach ($attribut?->all() ?? [] as $a) {
            if (!is_object($a) || empty($a->mail) || !str_contains($a->mail, '@')) continue;
            $res[] = ['nom' => trim((string) $a->personal, " \"'"), 'email' => strtolower($a->mail)];
        }
        return $res;
    }

    private static function date(Message $m): ?Carbon
    {
        try {
            $d = $m->getDate()?->first();
            return $d ? Carbon::instance($d)->setTimezone(config('app.timezone')) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function references(Message $m): string
    {
        $refs = $m->getReferences()?->all() ?? [];
        return implode(' ', array_map(fn($r) => '<'.trim((string) $r, '<> ').'>', array_filter($refs)));
    }

    /** Concatène les messages de la chaîne d'exceptions (la cause réelle est souvent en « previous »). */
    private static function chaine(\Throwable $e): string
    {
        $msgs = [];
        for ($x = $e; $x; $x = $x->getPrevious()) $msgs[] = $x->getMessage();
        return implode(' | ', array_unique(array_filter($msgs)));
    }

    /** Message d'erreur lisible à partir de l'erreur de connexion IMAP. */
    public static function messageErreur(string $erreur): string
    {
        $e = strtolower($erreur);
        return match (true) {
            str_contains($e, 'certificate') || str_contains($e, 'ssl operation failed') || str_contains($e, 'crypto')
                => "Certificat SSL du serveur invalide ou non reconnu : vérifiez l'adresse exacte du serveur (elle doit correspondre au certificat), le port et le chiffrement.",
            str_contains($e, 'authenticat') || str_contains($e, 'login') || str_contains($e, 'credentials') || str_contains($e, 'no such user')
                => "Identifiant ou mot de passe refusé par le serveur. (Gmail / Outlook : utilisez un mot de passe d'application.)",
            str_contains($e, 'getaddrinfo') || str_contains($e, 'name or service') || str_contains($e, 'nodename')
                => "Serveur introuvable : vérifiez l'adresse du serveur IMAP.",
            str_contains($e, 'connect') || str_contains($e, 'timed out') || str_contains($e, 'timeout') || str_contains($e, 'refused')
                => "Serveur injoignable : vérifiez l'adresse du serveur, le port et le chiffrement.",
            default => 'Erreur IMAP : '.$erreur,
        };
    }
}
