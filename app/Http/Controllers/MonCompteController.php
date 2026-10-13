<?php

namespace App\Http\Controllers;

use App\Models\Intranet\MailCompte;
use App\Models\Intranet\MailSignature;
use App\Models\Intranet\MailSignatureModele;
use App\Services\Mail\Signature;
use App\Services\Mail\BoiteMail;
use App\Services\Mail\EnvoiMail;
use App\Services\Mail\MailException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Profil de l'utilisateur connecté : informations + comptes mail professionnels
 * connectés (un ou plusieurs, IMAP + SMTP, mot de passe chiffré en base).
 */
class MonCompteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('roles:id,name');
        $comptes = $user->comptesMail()->get();
        $signatures = $user->signaturesMail()->with('modele')->get();
        // Modèles d'entreprise actifs pas encore ajoutés
        $modeles = MailSignatureModele::where('actif', true)->whereNotIn('id', $signatures->pluck('modele_id')->filter())->orderBy('nom')->get();
        return view('mon-compte.index', compact('user', 'comptes', 'signatures', 'modeles'));
    }

    public function storeMail(Request $request)
    {
        $user = $request->user();
        $data = $this->valider($request);

        $compte = new MailCompte($data + ['user_id' => $user->id, 'protocole' => 'imap', 'is_shared' => false]);
        if ($erreur = $this->tester($compte)) {
            return back()->withInput()->with('error', $erreur)->with('mail_modal', 'create');
        }

        DB::transaction(function () use ($compte, $user) {
            $compte->is_default = !$user->comptesMail()->exists() || $compte->is_default;
            if ($compte->is_default) $user->comptesMail()->update(['is_default' => false]);
            $compte->derniere_connexion_at = now();
            $compte->save();
        });

        return redirect()->route('mon-compte.index')->with('success', "Compte {$compte->email} connecté.");
    }

    public function updateMail(Request $request, MailCompte $compte)
    {
        $this->autoriser($request, $compte);
        $data = $this->valider($request, $compte);
        if (($data['mot_de_passe'] ?? '') === '') unset($data['mot_de_passe']); // vide = inchangé

        $compte->fill($data);
        if ($compte->isDirty(['serveur_entrant', 'port_entrant', 'ssl_entrant', 'serveur_sortant', 'port_sortant', 'ssl_sortant', 'identifiant', 'mot_de_passe'])
            && ($erreur = $this->tester($compte))) {
            return back()->withInput()->with('error', $erreur)->with('mail_modal', 'edit-'.$compte->id);
        }

        DB::transaction(function () use ($compte, $request) {
            if ($compte->is_default) $request->user()->comptesMail()->whereKeyNot($compte->id)->update(['is_default' => false]);
            $compte->derniere_erreur = null;
            $compte->save();
        });

        return redirect()->route('mon-compte.index')->with('success', "Compte {$compte->email} mis à jour.");
    }

    public function testerMail(Request $request, MailCompte $compte)
    {
        $this->autoriser($request, $compte);
        $erreur = $this->tester($compte);
        $compte->forceFill($erreur ? ['derniere_erreur' => $erreur] : ['derniere_erreur' => null, 'derniere_connexion_at' => now()])->save();

        return back()->with($erreur ? 'error' : 'success', $erreur ?: "Connexion à {$compte->email} réussie (réception et envoi).");
    }

    public function defautMail(Request $request, MailCompte $compte)
    {
        $this->autoriser($request, $compte);
        DB::transaction(function () use ($request, $compte) {
            $request->user()->comptesMail()->update(['is_default' => false]);
            $compte->update(['is_default' => true]);
        });
        return back()->with('success', "{$compte->email} est maintenant le compte par défaut.");
    }

    public function destroyMail(Request $request, MailCompte $compte)
    {
        $this->autoriser($request, $compte);
        DB::transaction(function () use ($request, $compte) {
            $compte->delete();
            // Le premier compte restant devient le compte par défaut
            if ($compte->is_default && ($suivant = $request->user()->comptesMail()->first())) {
                $suivant->update(['is_default' => true]);
            }
        });
        return back()->with('success', "Compte {$compte->email} déconnecté. Les mails restent sur le serveur.");
    }

    // ═══ Signatures ═════════════════════════════════════

    public function storeSignature(Request $request)
    {
        $user = $request->user();
        $data = $this->validerSignature($request);

        DB::transaction(function () use ($user, $data) {
            // La première signature devient la signature par défaut
            $defaut = $data['par_defaut'] || !$user->signaturesMail()->exists();
            if ($defaut) $user->signaturesMail()->update(['par_defaut' => false]);
            $user->signaturesMail()->create($data['attributs'] + ['par_defaut' => $defaut]);
        });

        return redirect()->to(route('mon-compte.index').'#signatures')->with('success', "Signature « {$data['attributs']['nom']} » créée.");
    }

    public function updateSignature(Request $request, MailSignature $signature)
    {
        $this->autoriserSignature($request, $signature);
        abort_if($signature->modele_id, 403, "Une signature issue d'un modèle d'entreprise n'est pas modifiable.");
        $data = $this->validerSignature($request, $signature);

        DB::transaction(function () use ($request, $signature, $data) {
            if ($data['par_defaut']) $request->user()->signaturesMail()->whereKeyNot($signature->id)->update(['par_defaut' => false]);
            $signature->update($data['attributs'] + ['par_defaut' => $data['par_defaut'] || $signature->par_defaut]);
        });

        return redirect()->to(route('mon-compte.index').'#signatures')->with('success', "Signature « {$signature->nom} » mise à jour.");
    }

    /** Ajoute à ses signatures un modèle d'entreprise actif. */
    public function ajouterModele(Request $request, MailSignatureModele $modele)
    {
        abort_unless($modele->actif, 404);
        $user = $request->user();
        if ($user->signaturesMail()->where('modele_id', $modele->id)->exists()) {
            return redirect()->to(route('mon-compte.index').'#signatures')->with('error', 'Ce modèle figure déjà dans vos signatures.');
        }
        DB::transaction(function () use ($user, $modele) {
            $defaut = !$user->signaturesMail()->exists();
            $user->signaturesMail()->create([
                'nom' => self::nomLibre($user, $modele->nom), 'modele_id' => $modele->id, 'contenu' => '', 'par_defaut' => $defaut,
            ]);
        });
        return redirect()->to(route('mon-compte.index').'#signatures')->with('success', "Modèle « {$modele->nom} » ajouté à vos signatures.");
    }

    /** Nom de signature libre pour l'utilisateur (« Nom », sinon « Nom (2) »…). */
    public static function nomLibre(\App\Models\User $user, string $nom): string
    {
        $base = mb_substr($nom, 0, 90);
        $candidat = $base;
        for ($i = 2; $user->signaturesMail()->where('nom', $candidat)->exists(); $i++) $candidat = "{$base} ({$i})";
        return $candidat;
    }

    public function defautSignature(Request $request, MailSignature $signature)
    {
        $this->autoriserSignature($request, $signature);
        DB::transaction(function () use ($request, $signature) {
            $request->user()->signaturesMail()->update(['par_defaut' => false]);
            $signature->update(['par_defaut' => true]);
        });
        return redirect()->to(route('mon-compte.index').'#signatures')->with('success', "« {$signature->nom} » est maintenant la signature par défaut.");
    }

    public function destroySignature(Request $request, MailSignature $signature)
    {
        $this->autoriserSignature($request, $signature);
        abort_if($signature->estVerrouillee(), 403, "Cette signature est imposée par l'entreprise.");
        DB::transaction(function () use ($request, $signature) {
            $signature->delete();
            if ($signature->par_defaut && ($suivante = $request->user()->signaturesMail()->first())) {
                $suivante->update(['par_defaut' => true]);
            }
        });
        return redirect()->to(route('mon-compte.index').'#signatures')->with('success', "Signature « {$signature->nom} » supprimée.");
    }

    private function autoriserSignature(Request $request, MailSignature $signature): void
    {
        abort_unless($signature->user_id === $request->user()->id, 403);
    }

    /** Valide le formulaire (mode visuel ou HTML/CSS) et compile le contenu. */
    private function validerSignature(Request $request, ?MailSignature $signature = null): array
    {
        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:100',
                Rule::unique('mail_signatures', 'nom')->where('user_id', $request->user()->id)->ignore($signature?->id)],
            'source_html' => 'required|string|max:300000', // logo en data URI possible (réduit côté navigateur)
            'source_css'  => 'nullable|string|max:20000',
            'par_defaut'  => 'nullable|boolean',
        ], ['nom.unique' => 'Vous avez déjà une signature portant ce nom.', 'source_html.required' => 'La signature est vide.']);

        $contenu = Signature::compiler($data['source_html'], $data['source_css'] ?? null);
        if (trim(strip_tags($contenu, '<img>')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['source_html' => 'La signature est vide.']);
        }
        return [
            'par_defaut' => (bool) ($data['par_defaut'] ?? false),
            'attributs'  => ['nom' => $data['nom'], 'contenu' => $contenu, 'source_html' => $data['source_html'], 'source_css' => $data['source_css'] ?? null],
        ];
    }

    // ── Helpers ─────────────────────────────────────────

    private function autoriser(Request $request, MailCompte $compte): void
    {
        abort_unless($compte->user_id === $request->user()->id, 403);
    }

    private function valider(Request $request, ?MailCompte $compte = null): array
    {
        $data = $request->validate([
            'nom'             => 'nullable|string|max:100',
            'email'           => ['required', 'email', 'max:150',
                Rule::unique('intranet_mail_comptes', 'email')->where('user_id', $request->user()->id)->ignore($compte?->id)],
            'nom_expediteur'  => 'nullable|string|max:150',
            'serveur_entrant' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$/'],
            'port_entrant'    => 'required|integer|in:993,143', // ports mail standards uniquement (évite le sondage du réseau interne)
            'ssl_entrant'     => 'required|boolean',
            'serveur_sortant' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$/'],
            'port_sortant'    => 'required|integer|in:465,587,25,2525',
            'ssl_sortant'     => 'required|boolean',
            'identifiant'     => 'required|string|max:255',
            'mot_de_passe'    => [$compte ? 'nullable' : 'required', 'string', 'max:500'],
            'is_default'      => 'nullable|boolean',
        ], [
            'serveur_entrant.regex' => "L'adresse du serveur IMAP ne doit contenir que des lettres, chiffres, points et tirets.",
            'serveur_sortant.regex' => "L'adresse du serveur SMTP ne doit contenir que des lettres, chiffres, points et tirets.",
            'email.unique'          => 'Ce compte mail est déjà connecté à votre profil.',
            'port_entrant.in'       => 'Port IMAP accepté : 993 (SSL) ou 143 (STARTTLS).',
            'port_sortant.in'       => 'Port SMTP accepté : 465 (SSL), 587 (STARTTLS), 25 ou 2525.',
        ]);
        $data['nom'] = ($data['nom'] ?? null) ?: $data['email'];
        $data['is_default'] = (bool) ($data['is_default'] ?? false);
        return $data;
    }

    /** Teste IMAP puis SMTP ; retourne le message d'erreur, ou null si tout est OK. */
    private function tester(MailCompte $compte): ?string
    {
        try {
            app(BoiteMail::class, ['compte' => $compte])->tester();
        } catch (MailException $e) {
            return 'Réception (IMAP) : '.$e->getMessage();
        }
        try {
            app(EnvoiMail::class, ['compte' => $compte])->tester();
        } catch (MailException $e) {
            return 'Envoi (SMTP) : '.$e->getMessage();
        }
        return null;
    }
}
