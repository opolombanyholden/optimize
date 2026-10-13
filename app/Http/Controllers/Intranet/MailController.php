<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\MailCompte;
use App\Services\Mail\BoiteMail;
use App\Services\Mail\CrmMail;
use App\Services\Mail\MailLiens;
use App\Models\Intranet\MailSuivi;
use App\Models\User;
use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Services\Mail\EnvoiMail;
use App\Services\Mail\MailException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Webmail : lecture (IMAP) et envoi (SMTP) des comptes mail professionnels
 * connectés au profil de l'utilisateur (cf. MonCompteController).
 */
class MailController extends Controller
{
    private const PAR_PAGE = 25;

    /** Quotas des pièces jointes (affichés et vérifiés côté client, revérifiés ici). */
    public const QUOTA_TOTAL   = 25 * 1024 * 1024;  // par mail (limite courante des serveurs SMTP)
    public const QUOTA_FICHIER = 20 * 1024 * 1024;  // par fichier
    public const QUOTA_NOMBRE  = 10;                // fichiers par mail
    private const QUOTA_TEMPORAIRE = 60 * 1024 * 1024; // espace temporaire max par utilisateur

    public function index(Request $request)
    {
        $comptes = $request->user()->comptesMail()->get();
        if ($comptes->isEmpty()) {
            return view('intranet.mails.aucun-compte');
        }

        $compte  = $this->compteDemande($request, $comptes);
        $dossier = (string) $request->query('dossier', 'INBOX');
        $page    = max(1, (int) $request->query('page', 1));
        $q       = trim((string) $request->query('q', ''));

        $dossiers = [];
        $liste = ['total' => 0, 'messages' => []];
        $erreur = null;
        try {
            $boite = app(BoiteMail::class, ['compte' => $compte]);
            $dossiers = $boite->dossiers();
            $liste = $boite->lister($dossier, $page, self::PAR_PAGE, $q ?: null);
            if ($dossier === 'INBOX') $this->memoriserNonLus($compte, $boite->nonLus());
            $this->noterSucces($compte);
        } catch (MailException $e) {
            $erreur = $e->getMessage();
            $this->noterErreur($compte, $erreur);
        }

        $pages = (int) max(1, ceil($liste['total'] / self::PAR_PAGE));

        // Classement des mails affichés (priorité, liens…) : une requête pour la page
        $suivis = MailSuivi::withCount('liens')->where('compte_id', $compte->id)
            ->whereIn('message_id', collect($liste['messages'])->pluck('message_id')->filter())
            ->get()->keyBy('message_id');
        $nbATraiter = $this->requeteATraiter($request->user())->count();

        return view('intranet.mails.index', compact('comptes', 'compte', 'dossier', 'dossiers', 'liste', 'page', 'pages', 'q', 'erreur', 'suivis', 'nbATraiter'));
    }

    /** Vue « À traiter » : mails classés à traiter / en attente, ou prioritaires non traités (tous comptes). */
    public function aTraiter(Request $request)
    {
        $comptes = $request->user()->comptesMail()->get();
        if ($comptes->isEmpty()) return redirect()->route('intranet.mails.index');
        $compte = $this->compteDemande($request, $comptes);

        $suivis = $this->requeteATraiter($request->user())
            ->with(['compte:id,email', 'liens.liable'])
            ->orderByRaw('echeance IS NULL, echeance')
            ->orderByRaw("CASE priorite WHEN 'urgente' THEN 0 WHEN 'haute' THEN 1 WHEN 'normale' THEN 2 ELSE 3 END")
            ->orderByDesc('date_mail')
            ->limit(200)->get();

        $dossiers = [];
        try { $dossiers = app(BoiteMail::class, ['compte' => $compte])->dossiers(); } catch (MailException) {}
        $nbATraiter = $suivis->count();
        $dossier = null;

        return view('intranet.mails.a-traiter', compact('comptes', 'compte', 'dossiers', 'suivis', 'nbATraiter', 'dossier'));
    }

    private function requeteATraiter(User $user)
    {
        return MailSuivi::where('user_id', $user->id)
            ->where(fn($q) => $q->whereIn('statut', ['a_traiter', 'en_attente'])
                ->orWhere(fn($w) => $w->whereIn('priorite', ['haute', 'urgente'])->where(fn($x) => $x->whereNull('statut')->orWhere('statut', '!=', 'traite'))));
    }

    public function show(Request $request, MailCompte $compte, int $uid)
    {
        $this->autoriser($request, $compte);
        $dossier = (string) $request->query('dossier', 'INBOX');

        try {
            $boite = app(BoiteMail::class, ['compte' => $compte]);
            $mail = $boite->lire($dossier, $uid);
            $dossiers = $boite->dossiers();
        } catch (MailException $e) {
            return redirect()->route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier])->with('error', $e->getMessage());
        }
        Cache::forget($this->cleNonLus($compte));

        // CRM : correspondants connus parmi les participants (hors adresse du compte)
        $crm = null;
        if ($request->user()->can('read:contact')) {
            $adresses = collect([$mail['de'], $mail['repondre_a'], $mail['a'], $mail['cc']])->flatten(1)->pluck('email')
                ->reject(fn($e) => $e === strtolower($compte->email))->unique()->values();
            $crm = CrmMail::correspondants($adresses->all()) + [
                'inconnus' => $adresses,
                'deja'     => $mail['message_id']
                    ? \App\Models\Intranet\CrmInteraction::where('mail_message_id', $mail['message_id'])->get(['interactable_type', 'interactable_id'])
                        ->map(fn($i) => array_search($i->interactable_type, array_map(fn($t) => $t[0], CrmMail::TYPES), true).':'.$i->interactable_id)->all()
                    : [],
            ];
            $connus = $crm['contacts']->pluck('email')->map(fn($e) => strtolower($e))
                ->merge($crm['organisations']->pluck('email')->filter()->map(fn($e) => strtolower($e)));
            $crm['inconnus'] = $adresses->diff($connus)->values();
        }

        $suivi = $mail['message_id']
            ? MailSuivi::with('liens.liable')->where('compte_id', $compte->id)->where('message_id', $mail['message_id'])->first()
            : null;
        $classement = $this->classementVue($suivi);
        $typesLiens = MailLiens::types($request->user());

        $comptes = $request->user()->comptesMail()->get();
        $nbATraiter = $this->requeteATraiter($request->user())->count();
        return view('intranet.mails.show', compact('comptes', 'compte', 'dossier', 'dossiers', 'mail', 'crm', 'suivi', 'classement', 'typesLiens', 'nbATraiter'));
    }

    /** Corps HTML d'un mail, servi dans une iframe sandboxée (aucun script, CSP stricte). */
    public function corps(Request $request, MailCompte $compte, int $uid)
    {
        $this->autoriser($request, $compte);
        $dossier = (string) $request->query('dossier', 'INBOX');
        $images  = $request->boolean('images');

        try {
            $mail = app(BoiteMail::class, ['compte' => $compte])->lire($dossier, $uid);
        } catch (MailException $e) {
            abort(404, $e->getMessage());
        }

        $html = $mail['html'] ?? '<pre style="white-space:pre-wrap;font-family:inherit;">'.e($mail['texte'] ?? '').'</pre>';
        $doc = '<!doctype html><html><head><meta charset="utf-8"><base target="_blank">'
            .'<style>body{font-family:Inter,Arial,sans-serif;font-size:14px;color:#1E293B;margin:0;padding:4px 2px;word-wrap:break-word;}img{max-width:100%;height:auto;}blockquote{border-left:3px solid #CBD5E1;margin:0 0 0 .5rem;padding-left:.75rem;color:#475569;}</style>'
            .'</head><body>'.$html.'</body></html>';

        // Images distantes bloquées par défaut (pixels de suivi) ; data: toujours autorisé (images intégrées)
        $img = $images ? 'data: https: http:' : 'data:';
        return response($doc, 200, [
            'Content-Type'            => 'text/html; charset=UTF-8',
            // Aucun script (default-src 'none'), aucun envoi de formulaire, intégrable uniquement par l'application.
            // L'iframe ajoute sandbox sans allow-scripts.
            'Content-Security-Policy' => "default-src 'none'; img-src {$img}; style-src 'unsafe-inline'; font-src data:; form-action 'none'; frame-ancestors 'self'",
            'X-Content-Type-Options'  => 'nosniff',
            'Cache-Control'           => 'private, no-store',
        ]);
    }

    public function piece(Request $request, MailCompte $compte, int $uid, string $partie)
    {
        $this->autoriser($request, $compte);
        abort_unless(preg_match('/^\d+(\.\d+)*$/', $partie), 404);

        try {
            [$nom, $mime, $data] = app(BoiteMail::class, ['compte' => $compte])->piece((string) $request->query('dossier', 'INBOX'), $uid, $partie);
        } catch (MailException $e) {
            abort(404, $e->getMessage());
        }

        // Téléchargement forcé : jamais d'affichage inline d'un contenu arbitraire
        return response()->streamDownload(fn() => print($data), $nom, [
            'Content-Type'           => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ═══ Rédaction / envoi ═══════════════════════════════════

    /** Nouveau mail, réponse (mode=repondre|repondre-tous) ou transfert (mode=transferer). */
    public function create(Request $request)
    {
        $comptes = $request->user()->comptesMail()->get();
        if ($comptes->isEmpty()) return redirect()->route('intranet.mails.index');

        $compte = $this->compteDemande($request, $comptes);
        $mode   = (string) $request->query('mode', 'nouveau');
        $brouillon = ['a' => (string) $request->query('a', ''), 'cc' => '', 'sujet' => '', 'citation' => '', 'en_reponse_a' => null, 'references' => null, 'pieces_origine' => []];
        $origine = null;

        if (in_array($mode, ['repondre', 'repondre-tous', 'transferer'], true) && $request->filled('uid')) {
            try {
                $origine = app(BoiteMail::class, ['compte' => $compte])->lire((string) $request->query('dossier', 'INBOX'), (int) $request->query('uid'));
            } catch (MailException $e) {
                return back()->with('error', $e->getMessage());
            }
            $brouillon = array_merge($brouillon, $this->preremplir($mode, $origine, $compte));
        }

        // Ouverture depuis une fiche CRM : cible pré-cochée pour l'historique
        $crmContexte = collect(['contact', 'organisation', 'opportunite'])
            ->filter(fn($t) => $request->filled($t))
            ->map(fn($t) => $t.':'.(int) $request->query($t))
            ->filter(fn($cle) => CrmMail::cible($cle, $request->user()))
            ->values()->all();

        // Classement : hérité du mail d'origine (réponse / transfert) + entités passées en contexte (?lien=type:id)
        $suiviOrigine = $origine && $origine['message_id']
            ? MailSuivi::with('liens.liable')->where('compte_id', $compte->id)->where('message_id', $origine['message_id'])->first()
            : null;
        $classement = $this->classementVue($suiviOrigine, heriter: true);
        $contexte = array_merge((array) $request->query('lien', []), $crmContexte);
        foreach ($contexte as $cle) {
            if (is_string($cle) && ($m = MailLiens::resoudre($cle, $request->user()))) {
                $n = MailLiens::normaliser($m);
                if (!collect($classement['liens'])->contains('cle', $n['cle'])) $classement['liens'][] = $n;
            }
        }

        return view('intranet.mails.create', [
            'classement'  => $classement,
            'typesLiens'  => MailLiens::types($request->user()),
            'crmContexte' => $crmContexte,
            'signatures' => $request->user()->signaturesMail()->with('modele', 'utilisateur')->get(),
            'quotas'    => ['total' => self::QUOTA_TOTAL, 'fichier' => self::QUOTA_FICHIER, 'nombre' => self::QUOTA_NOMBRE],
            'comptes'   => $comptes,
            'compte'    => $compte,
            'mode'      => $mode,
            'brouillon' => $brouillon,
            'origine'   => $origine,
            'dossier'   => (string) $request->query('dossier', 'INBOX'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'compte_id'      => 'required|integer',
            'a'              => 'required|string|max:2000',
            'cc'             => 'nullable|string|max:2000',
            'cci'            => 'nullable|string|max:2000',
            'sujet'          => 'nullable|string|max:255',
            'html'           => 'nullable|string|max:500000',
            'citation'       => 'nullable|string|max:5000000',
            'en_reponse_a'   => 'nullable|string|max:500',
            'references'     => 'nullable|string|max:5000',
            'origine_uid'    => 'nullable|integer',
            'origine_dossier'=> 'nullable|string|max:255',
            'pieces_origine' => 'nullable|array',
            'pieces_origine.*' => ['string', 'regex:/^\d+(\.\d+)*$/'],
            'pieces'         => 'nullable|array|max:'.self::QUOTA_NOMBRE,
            'pieces.*'       => 'file|max:'.(self::QUOTA_FICHIER / 1024),
            'pieces_tmp'     => 'nullable|array|max:'.self::QUOTA_NOMBRE,
            'pieces_tmp.*'   => 'uuid',
            'signature_id'   => 'nullable|integer',
            'accuse_lecture' => 'nullable|boolean',
            ...self::reglesClassement(),
            'crm_cibles'     => 'nullable|array|max:20',
            'crm_cibles.*'   => ['string', 'regex:/^(contact|organisation|opportunite):\d+$/'],
        ]);

        $compte = MailCompte::findOrFail($data['compte_id']);
        $this->autoriser($request, $compte);

        $destinataires = [];
        foreach (['a', 'cc', 'cci'] as $champ) {
            [$valides, $invalides] = self::adresses($data[$champ] ?? '');
            if ($invalides) {
                return back()->withInput()->with('error', 'Adresse(s) invalide(s) : '.implode(', ', $invalides));
            }
            $destinataires[$champ] = $valides;
        }
        if (!$destinataires['a']) return back()->withInput()->with('error', 'Indiquez au moins un destinataire.');

        $total = count($destinataires['a']) + count($destinataires['cc']) + count($destinataires['cci']);
        if ($total > 50) return back()->withInput()->with('error', 'Maximum 50 destinataires par envoi.');

        // Pièces : téléversées à l'avance (pieces_tmp), envoyées avec le formulaire (repli sans JS)
        // et pièces du mail d'origine (transfert)
        $pieces = [];
        foreach ($data['pieces_tmp'] ?? [] as $id) {
            $tmp = $this->pieceTemporaire($request, $id);
            if (!$tmp) return back()->withInput()->with('error', 'Une pièce jointe a expiré : joignez-la de nouveau.');
            $pieces[] = $tmp;
        }
        foreach ($request->file('pieces', []) as $f) {
            $pieces[] = ['nom' => $f->getClientOriginalName(), 'mime' => $f->getMimeType() ?: 'application/octet-stream', 'data' => $f->get()];
        }

        // Signature choisie (appartenant à l'utilisateur), insérée entre le texte et le message cité
        $signature = '';
        if (!empty($data['signature_id'])) {
            $sig = $request->user()->signaturesMail()->whereKey($data['signature_id'])->first();
            if ($sig) $signature = '<br><div class="signature">-- <br>'.$sig->html().'</div>';
        }
        $boite = app(BoiteMail::class, ['compte' => $compte]);
        try {
            if (!empty($data['pieces_origine']) && !empty($data['origine_uid'])) {
                foreach ($data['pieces_origine'] as $partie) {
                    [$nom, $mime, $contenu] = $boite->piece($data['origine_dossier'] ?? 'INBOX', (int) $data['origine_uid'], $partie);
                    $pieces[] = ['nom' => $nom, 'mime' => $mime, 'data' => $contenu];
                }
            }
            if (count($pieces) > self::QUOTA_NOMBRE) {
                return back()->withInput()->with('error', 'Maximum '.self::QUOTA_NOMBRE.' pièces jointes par mail.');
            }
            if (array_sum(array_map(fn($p) => strlen($p['data']), $pieces)) > self::QUOTA_TOTAL) {
                return back()->withInput()->with('error', 'Les pièces jointes dépassent '.(self::QUOTA_TOTAL / 1048576).' Mo au total.');
            }

            $mime = app(EnvoiMail::class, ['compte' => $compte])->envoyer([
                'a'            => $destinataires['a'],
                'cc'           => $destinataires['cc'],
                'cci'          => $destinataires['cci'],
                'sujet'        => trim((string) ($data['sujet'] ?? '')) ?: '(sans objet)',
                // Texte saisi (Quill) + message cité conservé tel quel (réponse / transfert)
                'html'         => ($data['html'] ?: '<p></p>').$signature.($data['citation'] ?? ''),
                'en_reponse_a' => $data['en_reponse_a'] ?? null,
                'references'   => $data['references'] ?? null,
                'priorite'     => $data['priorite'] ?? null,
                'accuse_lecture' => (bool) ($data['accuse_lecture'] ?? false),
            ], $pieces);
        } catch (MailException $e) {
            return back()->withInput()->with('error', "Envoi impossible : ".$e->getMessage());
        }

        foreach ($data['pieces_tmp'] ?? [] as $id) $this->supprimerTemporaire($request, $id);

        // Historique CRM des cibles cochées
        $journalises = 0;
        foreach (array_unique($data['crm_cibles'] ?? []) as $cle) {
            if ($cible = CrmMail::cible($cle, $request->user())) {
                $journalises += (int) CrmMail::journaliser($cible, [
                    'sujet'      => trim((string) ($data['sujet'] ?? '')) ?: '(sans objet)',
                    'html'       => $data['html'] ?? '',
                    'date'       => now(),
                    'message_id' => CrmMail::messageIdDepuisMime($mime),
                ], 'envoye', $compte, $request->user());
            }
        }

        // Classement + liens vers les modules (seulement si l'utilisateur a renseigné quelque chose)
        $liesMsg = '';
        if ($this->aUnClassement($data)) {
            [, $nbLiens] = $this->enregistrerSuivi($request->user(), $compte, [
                'message_id'    => CrmMail::messageIdDepuisMime($mime),
                'sujet'         => trim((string) ($data['sujet'] ?? '')) ?: '(sans objet)',
                'correspondant' => collect($destinataires['a'])->map(fn($a) => $a->getAddress())->implode(', '),
                'date'          => now(),
                'sens'          => 'envoye',
                'html'          => $data['html'] ?? '',
            ], $data);
            if ($nbLiens) $liesMsg = " Lié à {$nbLiens} élément(s).";
        }

        // Copie dans « Envoyés » (la plupart des serveurs ne le font pas pour SMTP) — non bloquant
        $copie = false;
        try {
            $copie = $boite->ajouterAuxEnvoyes($mime);
        } catch (MailException) {}

        return redirect()->route('intranet.mails.index', ['compte' => $compte->id])
            ->with('success', 'Mail envoyé.'.$liesMsg.($journalises ? " Ajouté à l'historique CRM ({$journalises} fiche(s))." : '').($copie ? '' : ' (Copie non enregistrée dans « Envoyés » : dossier introuvable.)'));
    }

    // ═══ Classement & liens vers les modules ═════════════════

    /** Classe un mail lu : priorité, catégorie, statut, échéance, note, liens. */
    public function classer(Request $request, MailCompte $compte, int $uid)
    {
        $this->autoriser($request, $compte);
        $data = $request->validate(['dossier' => 'required|string|max:255', ...self::reglesClassement()]);

        try {
            $mail = app(BoiteMail::class, ['compte' => $compte])->lire($data['dossier'], $uid);
        } catch (MailException $e) {
            return back()->with('error', $e->getMessage());
        }
        if (!$mail['message_id']) return back()->with('error', "Ce mail n'a pas d'identifiant (Message-ID) : il ne peut pas être classé.");

        $exp = $mail['de'][0] ?? ['nom' => '', 'email' => ''];
        [, $nbLiens, $refus] = $this->enregistrerSuivi($request->user(), $compte, [
            'message_id'    => $mail['message_id'],
            'sujet'         => $mail['sujet'],
            'correspondant' => trim(($exp['nom'] ? $exp['nom'].' ' : '').'<'.$exp['email'].'>'),
            'date'          => $mail['date'],
            'sens'          => $exp['email'] === strtolower($compte->email) ? 'envoye' : 'recu',
            'uid'           => $uid,
            'dossier'       => $data['dossier'],
            'texte'         => $mail['texte'],
            'html'          => $mail['html'],
        ], $data);

        return back()->with('success', 'Classement enregistré'.($nbLiens ? " · {$nbLiens} élément(s) lié(s)" : '').'.'.($refus ? " {$refus} lien(s) ignoré(s) (accès refusé)." : ''));
    }

    /** Recherche d'entités liables (projets, tâches, courriers, opportunités, dépenses…). */
    public function rechercherLiens(Request $request)
    {
        $type = $request->query('type') ?: null;
        $q = (string) $request->query('q', '');
        if (!$type && mb_strlen(trim($q)) < 2) return response()->json([]);
        return response()->json(MailLiens::rechercher($request->user(), $q, $type));
    }

    private static function reglesClassement(): array
    {
        return [
            'priorite'     => 'nullable|in:basse,normale,haute,urgente',
            'categorie'    => 'nullable|string|max:50',
            'statut'       => 'nullable|in:a_traiter,en_attente,traite',
            'echeance'     => 'nullable|date|after_or_equal:2000-01-01',
            'note'         => 'nullable|string|max:5000',
            'confidentiel' => 'nullable|boolean',
            'liens'        => 'nullable|array|max:20',
            'liens.*'      => ['string', 'regex:/^[a-z]+:\d+$/'],
        ];
    }

    private function aUnClassement(array $data): bool
    {
        return !empty($data['liens']) || ($data['priorite'] ?? 'normale') !== 'normale'
            || !empty($data['categorie']) || !empty($data['statut']) || !empty($data['echeance'])
            || trim((string) ($data['note'] ?? '')) !== '' || !empty($data['confidentiel']);
    }

    /**
     * Crée / met à jour le classement d'un mail et synchronise ses liens (seules les entités
     * accessibles à l'utilisateur sont liées). Les liens vers le CRM alimentent aussi l'historique.
     * @return array{0: MailSuivi, 1: int, 2: int} [suivi, nb de liens, nb refusés]
     */
    private function enregistrerSuivi(User $user, MailCompte $compte, array $mail, array $data): array
    {
        $refus = 0;
        $modeles = [];
        foreach (array_unique($data['liens'] ?? []) as $cle) {
            ($m = MailLiens::resoudre($cle, $user)) ? $modeles[$cle] = $m : $refus++;
        }

        $suivi = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $compte, $mail, $data, $modeles) {
            $suivi = MailSuivi::updateOrCreate(
                ['compte_id' => $compte->id, 'message_id' => $mail['message_id']],
                [
                    'user_id'       => $user->id,
                    'sujet'         => mb_substr($mail['sujet'], 0, 255),
                    'correspondant' => mb_substr((string) ($mail['correspondant'] ?? ''), 0, 255) ?: null,
                    'date_mail'     => $mail['date'] ?? now(),
                    'sens'          => $mail['sens'],
                    'uid'           => $mail['uid'] ?? null,
                    'dossier'       => $mail['dossier'] ?? null,
                    'priorite'      => $data['priorite'] ?? 'normale',
                    'categorie'     => ($data['categorie'] ?? '') !== '' ? $data['categorie'] : null,
                    'statut'        => $data['statut'] ?? null,
                    'echeance'      => $data['echeance'] ?? null,
                    'note'          => trim((string) ($data['note'] ?? '')) ?: null,
                    'confidentiel'  => (bool) ($data['confidentiel'] ?? false),
                ]
            );

            $existants = $suivi->liens()->get()->keyBy(fn($l) => $l->liable_type.'#'.$l->liable_id);
            $voulus = collect($modeles)->keyBy(fn($m) => $m->getMorphClass().'#'.$m->getKey());
            foreach ($existants->diffKeys($voulus) as $l) $l->delete();
            foreach ($voulus->diffKeys($existants) as $m) {
                $suivi->liens()->create(['liable_type' => $m->getMorphClass(), 'liable_id' => $m->getKey(), 'created_by' => $user->id]);
            }
            return $suivi;
        });

        // Liens CRM → historique des interactions (sans doublon, si l'utilisateur peut alimenter la fiche)
        foreach (array_keys($modeles) as $cle) {
            if (preg_match('/^(contact|organisation|opportunite):/', $cle) && ($cible = CrmMail::cible($cle, $user))) {
                CrmMail::journaliser($cible, $mail + ['texte' => $mail['texte'] ?? null, 'html' => $mail['html'] ?? null], $mail['sens'], $compte, $user, $mail['uid'] ?? null, $mail['dossier'] ?? null);
            }
        }

        return [$suivi, count($modeles), $refus];
    }

    /** Données du classement pour les vues (liens normalisés, entités supprimées ignorées). */
    private function classementVue(?MailSuivi $suivi, bool $heriter = false): array
    {
        $liens = [];
        foreach ($suivi?->liens ?? [] as $l) {
            if ($l->liable) $liens[] = MailLiens::normaliser($l->liable);
        }
        if ($heriter) {
            // Une réponse reprend les liens, la priorité, la catégorie et la confidentialité — pas l'échéance ni la note
            return ['priorite' => $suivi->priorite ?? 'normale', 'categorie' => $suivi?->categorie, 'statut' => null,
                'echeance' => null, 'note' => null, 'confidentiel' => (bool) $suivi?->confidentiel, 'liens' => $liens];
        }
        return ['priorite' => $suivi->priorite ?? 'normale', 'categorie' => $suivi?->categorie, 'statut' => $suivi?->statut,
            'echeance' => $suivi?->echeance?->toDateString(), 'note' => $suivi?->note, 'confidentiel' => (bool) $suivi?->confidentiel, 'liens' => $liens];
    }

    // ═══ CRM ═══════════════════════════════════════════════════

    /** Ajoute un mail reçu à l'historique CRM des fiches cochées. */
    public function journaliserCrm(Request $request, MailCompte $compte, int $uid)
    {
        $this->autoriser($request, $compte);
        $data = $request->validate([
            'dossier'  => 'required|string|max:255',
            'cibles'   => 'required|array|min:1|max:20',
            'cibles.*' => ['string', 'regex:/^(contact|organisation|opportunite):\d+$/'],
        ], ['cibles.required' => 'Cochez au moins une fiche CRM.']);

        try {
            // Relu sur le serveur : le contenu journalisé ne vient pas du navigateur
            $mail = app(BoiteMail::class, ['compte' => $compte])->lire($data['dossier'], $uid);
        } catch (MailException $e) {
            return back()->with('error', $e->getMessage());
        }

        $exp = $mail['de'][0]['email'] ?? '';
        $sens = $exp === strtolower($compte->email) ? 'envoye' : 'recu';
        $ajouts = 0;
        $refus = 0;
        foreach (array_unique($data['cibles']) as $cle) {
            $cible = CrmMail::cible($cle, $request->user());
            if (!$cible) { $refus++; continue; }
            $ajouts += (int) CrmMail::journaliser($cible, $mail, $sens, $compte, $request->user(), $uid, $data['dossier']);
        }

        $msg = $ajouts ? "Mail ajouté à l'historique de {$ajouts} fiche(s) CRM." : "Ce mail figurait déjà dans l'historique de ces fiches.";
        if ($refus) $msg .= " {$refus} fiche(s) ignorée(s) (droits insuffisants ou fiche introuvable).";
        return back()->with($ajouts ? 'success' : 'error', $msg);
    }

    /** Autocomplétion des destinataires : contacts et organisations CRM ayant une adresse mail. */
    public function destinataires(Request $request)
    {
        if (!$request->user()->can('read:contact')) return response()->json([]);
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) return response()->json([]);
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';

        $contacts = Contact::with('organisation:id,nom')->whereNotNull('email')->where('email', '!=', '')
            ->where(fn($w) => $w->where('nom', 'ilike', $like)->orWhere('prenoms', 'ilike', $like)->orWhere('email', 'ilike', $like))
            ->orderBy('nom')->limit(15)->get(['id', 'nom', 'prenoms', 'email', 'organisation_id'])
            ->map(fn($c) => [
                'valeur' => trim("{$c->prenoms} {$c->nom}").' <'.strtolower($c->email).'>',
                'nom'    => trim("{$c->prenoms} {$c->nom}"),
                'email'  => strtolower($c->email),
                'info'   => $c->organisation?->nom ?? 'Contact',
            ]);

        $orgs = ContactOrganisation::whereNotNull('email')->where('email', '!=', '')
            ->where(fn($w) => $w->where('nom', 'ilike', $like)->orWhere('email', 'ilike', $like))
            ->orderBy('nom')->limit(5)->get(['id', 'nom', 'email'])
            ->map(fn($o) => ['valeur' => $o->nom.' <'.strtolower($o->email).'>', 'nom' => $o->nom, 'email' => strtolower($o->email), 'info' => 'Organisation']);

        return response()->json($contacts->concat($orgs)->unique('email')->values());
    }

    /** Fiches CRM correspondant aux destinataires saisis (cases « historique CRM » de la rédaction). */
    public function crmCorrespondants(Request $request)
    {
        if (!$request->user()->can('read:contact')) return response()->json([]);
        $emails = array_slice((array) $request->query('emails', []), 0, 50);
        $c = CrmMail::correspondants($emails);
        $user = $request->user();

        $res = [];
        foreach ($c['contacts'] as $x) {
            $res[] = ['cle' => 'contact:'.$x->id, 'type' => 'Contact', 'label' => trim("{$x->prenoms} {$x->nom}"), 'info' => $x->organisation?->nom, 'droit' => $user->can('update:contact')];
        }
        foreach ($c['organisations'] as $x) {
            $res[] = ['cle' => 'organisation:'.$x->id, 'type' => 'Organisation', 'label' => $x->nom, 'info' => null, 'droit' => $user->can('update:organisation_crm')];
        }
        foreach ($c['opportunites'] as $x) {
            $res[] = ['cle' => 'opportunite:'.$x->id, 'type' => 'Opportunité', 'label' => $x->titre, 'info' => $x->etape?->nom, 'droit' => $user->can('update:opportunite')];
        }
        return response()->json($res);
    }

    // ═══ Pièces jointes téléversées à l'avance (aperçu + progression) ═══

    /** Téléverse une pièce jointe dans l'espace temporaire privé de l'utilisateur. */
    public function storePiece(Request $request)
    {
        $request->validate(['fichier' => 'required|file|max:'.(self::QUOTA_FICHIER / 1024)], [
            'fichier.max' => 'Fichier trop volumineux (maximum '.(self::QUOTA_FICHIER / 1048576).' Mo).',
        ]);
        $f = $request->file('fichier');
        $dir = $this->dossierTemporaire($request);
        $this->purgerTemporaires($dir);

        $occupe = array_sum(array_map(fn($p) => Storage::disk('local')->size($p), Storage::disk('local')->files($dir)));
        if ($occupe + $f->getSize() > self::QUOTA_TEMPORAIRE) {
            return response()->json(['message' => 'Espace temporaire plein : envoyez ou retirez des pièces jointes en attente.'], 422);
        }

        $id = (string) Str::uuid();
        Storage::disk('local')->putFileAs($dir, $f, $id);
        Storage::disk('local')->put("{$dir}/{$id}.json", json_encode([
            'nom'  => mb_substr($f->getClientOriginalName(), 0, 200),
            'mime' => $f->getMimeType() ?: 'application/octet-stream',
        ]));

        return response()->json(['id' => $id, 'nom' => $f->getClientOriginalName(), 'taille' => $f->getSize()]);
    }

    public function destroyPiece(Request $request, string $id)
    {
        abort_unless(Str::isUuid($id), 404);
        $this->supprimerTemporaire($request, $id);
        return response()->json(['ok' => true]);
    }

    private function dossierTemporaire(Request $request): string
    {
        return 'mail-pieces/'.$request->user()->id;
    }

    /** @return array{nom:string, mime:string, data:string}|null */
    private function pieceTemporaire(Request $request, string $id): ?array
    {
        $chemin = $this->dossierTemporaire($request).'/'.$id;
        $disk = Storage::disk('local');
        if (!Str::isUuid($id) || !$disk->exists($chemin) || !$disk->exists($chemin.'.json')) return null;
        $meta = json_decode($disk->get($chemin.'.json'), true) ?: [];
        return ['nom' => $meta['nom'] ?? 'piece-jointe', 'mime' => $meta['mime'] ?? 'application/octet-stream', 'data' => $disk->get($chemin)];
    }

    private function supprimerTemporaire(Request $request, string $id): void
    {
        if (!Str::isUuid($id)) return;
        $chemin = $this->dossierTemporaire($request).'/'.$id;
        Storage::disk('local')->delete([$chemin, $chemin.'.json']);
    }

    /** Supprime les pièces abandonnées depuis plus de 24 h. */
    private function purgerTemporaires(string $dir): void
    {
        $disk = Storage::disk('local');
        foreach ($disk->files($dir) as $p) {
            if ($disk->lastModified($p) < now()->subDay()->getTimestamp()) $disk->delete($p);
        }
    }

    // ═══ Actions ═════════════════════════════════════════════

    public function destroy(Request $request, MailCompte $compte, int $uid)
    {
        $this->autoriser($request, $compte);
        $dossier = (string) $request->input('dossier', 'INBOX');
        try {
            app(BoiteMail::class, ['compte' => $compte])->supprimer($dossier, $uid);
        } catch (MailException $e) {
            return back()->with('error', $e->getMessage());
        }
        Cache::forget($this->cleNonLus($compte));
        return redirect()->route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier])->with('success', 'Mail supprimé.');
    }

    public function marquer(Request $request, MailCompte $compte, int $uid)
    {
        $this->autoriser($request, $compte);
        $data = $request->validate(['dossier' => 'required|string|max:255', 'lu' => 'nullable|boolean', 'suivi' => 'nullable|boolean']);
        try {
            $boite = app(BoiteMail::class, ['compte' => $compte]);
            if ($request->has('lu'))    $boite->marquerLu($data['dossier'], $uid, (bool) $data['lu']);
            if ($request->has('suivi')) $boite->marquerSuivi($data['dossier'], $uid, (bool) $data['suivi']);
        } catch (MailException $e) {
            return back()->with('error', $e->getMessage());
        }
        Cache::forget($this->cleNonLus($compte));

        return $request->boolean('retour_liste')
            ? redirect()->route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $data['dossier']])
            : back();
    }

    /** Total des non-lus (boîtes de réception de tous les comptes) — badges, mis en cache 2 min. */
    public function nonLus(Request $request)
    {
        $total = 0;
        foreach ($request->user()->comptesMail()->get() as $compte) {
            $total += Cache::remember($this->cleNonLus($compte), 120, function () use ($compte) {
                try {
                    return app(BoiteMail::class, ['compte' => $compte])->nonLus();
                } catch (MailException) {
                    return 0;
                }
            });
        }
        return response()->json(['total' => $total]);
    }

    // ═══ Helpers ═════════════════════════════════════════════

    private function autoriser(Request $request, MailCompte $compte): void
    {
        abort_unless($compte->user_id === $request->user()->id, 403);
    }

    private function compteDemande(Request $request, $comptes): MailCompte
    {
        $id = (int) $request->query('compte', 0);
        return $comptes->firstWhere('id', $id) ?? $comptes->firstWhere('is_default', true) ?? $comptes->first();
    }

    private function cleNonLus(MailCompte $compte): string
    {
        return "mail_non_lus_{$compte->id}";
    }

    private function memoriserNonLus(MailCompte $compte, int $n): void
    {
        Cache::put($this->cleNonLus($compte), $n, 120);
    }

    private function noterSucces(MailCompte $compte): void
    {
        $compte->forceFill(['derniere_connexion_at' => now(), 'derniere_erreur' => null])->saveQuietly();
    }

    private function noterErreur(MailCompte $compte, string $erreur): void
    {
        $compte->forceFill(['derniere_erreur' => $erreur])->saveQuietly();
    }

    private function preremplir(string $mode, array $o, MailCompte $compte): array
    {
        $fmt = fn(array $l) => implode(', ', array_map(fn($a) => $a['nom'] ? "{$a['nom']} <{$a['email']}>" : $a['email'], $l));
        $date = $o['date']?->translatedFormat('l j F Y à H:i') ?? '';
        $auteur = $fmt($o['de']);
        $corpsOrigine = $o['html'] ?? nl2br(e($o['texte'] ?? ''));
        // Les images intégrées (data:) sont conservées ; tout le reste du HTML d'origine est cité tel quel
        $corpsOrigine = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $corpsOrigine);

        if ($mode === 'transferer') {
            return [
                'sujet' => preg_match('/^(tr|fwd?)\s*:/i', $o['sujet']) ? $o['sujet'] : 'TR : '.$o['sujet'],
                'citation' => '<br><p>---------- Message transféré ----------<br>De : '.e($auteur).'<br>Date : '.e($date).'<br>Objet : '.e($o['sujet']).'<br>À : '.e($fmt($o['a'])).'</p>'.$corpsOrigine,
                'pieces_origine' => $o['pieces'],
            ];
        }

        $repondreA = $o['repondre_a'] ?: $o['de'];
        $a = $repondreA;
        $cc = [];
        if ($mode === 'repondre-tous') {
            $moi = strtolower($compte->email);
            $a = array_merge($repondreA, array_filter($o['a'], fn($x) => $x['email'] !== $moi));
            $cc = array_filter($o['cc'], fn($x) => $x['email'] !== $moi);
        }

        return [
            'a'            => $fmt(array_values($a)),
            'cc'           => $fmt(array_values($cc)),
            'sujet'        => preg_match('/^(re|ré)\s*:/iu', $o['sujet']) ? $o['sujet'] : 'RE : '.$o['sujet'],
            'citation'     => '<br><p>Le '.e($date).', '.e($auteur).' a écrit :</p><blockquote style="margin:0 0 0 .5rem;padding-left:.75rem;border-left:3px solid #CBD5E1;">'.$corpsOrigine.'</blockquote>',
            'en_reponse_a' => $o['message_id'] ?: null,
            'references'   => $o['references'],
        ];
    }

    /**
     * Découpe une liste « Nom <email>, autre@x.fr ; … » en adresses valides.
     * @return array{0: array<int, \Symfony\Component\Mime\Address>, 1: array<int, string>}
     */
    private static function adresses(string $liste): array
    {
        $valides = [];
        $invalides = [];
        foreach (preg_split('/[,;\n]+/', $liste) as $morceau) {
            $morceau = trim($morceau);
            if ($morceau === '') continue;
            $nom = '';
            $email = $morceau;
            if (preg_match('/^(.*)<([^>]+)>$/', $morceau, $m)) {
                $nom = trim($m[1], " \"'");
                $email = trim($m[2]);
            }
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valides[] = new \Symfony\Component\Mime\Address($email, $nom);
            } else {
                $invalides[] = $morceau;
            }
        }
        return [$valides, $invalides];
    }
}
