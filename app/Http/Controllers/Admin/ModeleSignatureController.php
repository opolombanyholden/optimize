<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MonCompteController;
use App\Models\Intranet\MailSignature;
use App\Models\Intranet\MailSignatureModele;
use App\Models\User;
use App\Services\Mail\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Modèles de signature d'entreprise : HTML/CSS avec variables {{prenom}}, {{poste}}…,
 * appliqués à tous les utilisateurs actifs. Les signatures liées sont calculées à la volée :
 * modifier le modèle met à jour la signature de chacun.
 */
class ModeleSignatureController extends Controller
{
    public function index()
    {
        $modeles = MailSignatureModele::withCount('signatures')
            ->withCount(['signatures as par_defaut_count' => fn($q) => $q->where('par_defaut', true)])
            ->orderBy('nom')->get();
        $utilisateursActifs = User::where('statut', 1)->count();
        return view('admin.signatures-modeles.index', compact('modeles', 'utilisateursActifs'));
    }

    public function create()
    {
        return view('admin.signatures-modeles.form', ['modele' => new MailSignatureModele([
            'actif' => true, 'source_html' => self::exemple(), 'source_css' => self::exempleCss(),
        ])]);
    }

    public function store(Request $request)
    {
        $modele = MailSignatureModele::create($this->valider($request) + ['created_by' => $request->user()->id]);
        return redirect()->route('admin.signatures-modeles.index')->with('success', "Modèle « {$modele->nom} » créé. Appliquez-le pour l'ajouter aux signatures des utilisateurs.");
    }

    public function edit(MailSignatureModele $modele)
    {
        return view('admin.signatures-modeles.form', compact('modele'));
    }

    public function update(Request $request, MailSignatureModele $modele)
    {
        $modele->update($this->valider($request, $modele));
        $n = $modele->signatures()->count();
        return redirect()->route('admin.signatures-modeles.index')
            ->with('success', "Modèle « {$modele->nom} » mis à jour".($n ? " — la signature de {$n} utilisateur(s) est mise à jour automatiquement." : '.'));
    }

    /** Ajoute (ou conserve) la signature du modèle pour chaque utilisateur actif, éventuellement par défaut. */
    public function appliquer(Request $request, MailSignatureModele $modele)
    {
        $data = $request->validate(['par_defaut' => 'nullable|boolean']);
        abort_unless($modele->actif, 422, 'Activez le modèle avant de l\'appliquer.');
        $parDefaut = (bool) ($data['par_defaut'] ?? false);

        $ajouts = 0;
        DB::transaction(function () use ($modele, $parDefaut, &$ajouts) {
            User::where('statut', 1)->orderBy('id')->chunk(200, function ($users) use ($modele, $parDefaut, &$ajouts) {
                foreach ($users as $user) {
                    $sig = MailSignature::where('user_id', $user->id)->where('modele_id', $modele->id)->first();
                    if (!$sig) {
                        $sig = MailSignature::create([
                            'user_id' => $user->id, 'modele_id' => $modele->id, 'contenu' => '',
                            'nom' => MonCompteController::nomLibre($user, $modele->nom), 'par_defaut' => false,
                        ]);
                        $ajouts++;
                    }
                    // Défaut : demandé, ou l'utilisateur n'en a aucun
                    $aUnDefaut = MailSignature::where('user_id', $user->id)->where('par_defaut', true)->exists();
                    if ($parDefaut || !$aUnDefaut) {
                        MailSignature::where('user_id', $user->id)->whereKeyNot($sig->id)->update(['par_defaut' => false]);
                        $sig->update(['par_defaut' => true]);
                    }
                }
            });
        });

        return back()->with('success', "Modèle « {$modele->nom} » appliqué : {$ajouts} signature(s) ajoutée(s)".($parDefaut ? ', défini comme signature par défaut pour tous.' : '.'));
    }

    /** Retire le modèle des signatures de tous les utilisateurs (le modèle est conservé). */
    public function retirer(MailSignatureModele $modele)
    {
        $n = DB::transaction(function () use ($modele) {
            $users = $modele->signatures()->where('par_defaut', true)->pluck('user_id');
            $n = $modele->signatures()->delete();
            // Une autre signature devient la signature par défaut, s'il en reste une
            foreach ($users as $uid) {
                MailSignature::where('user_id', $uid)->orderBy('id')->first()?->update(['par_defaut' => true]);
            }
            return $n;
        });
        return back()->with('success', "Modèle retiré de {$n} signature(s).");
    }

    public function destroy(MailSignatureModele $modele)
    {
        $this->retirer($modele);
        $modele->delete();
        return redirect()->route('admin.signatures-modeles.index')->with('success', 'Modèle supprimé.');
    }

    private function valider(Request $request, ?MailSignatureModele $modele = null): array
    {
        $data = $request->validate([
            'nom'         => ['required', 'string', 'max:100', Rule::unique('mail_signature_modeles', 'nom')->ignore($modele?->id)],
            'source_html' => 'required|string|max:300000',
            'source_css'  => 'nullable|string|max:20000',
            'actif'       => 'nullable|boolean',
            'verrouille'  => 'nullable|boolean',
        ], ['nom.unique' => 'Un modèle porte déjà ce nom.', 'source_html.required' => 'Le modèle est vide.']);

        $contenu = Signature::compiler($data['source_html'], $data['source_css'] ?? null);
        if (trim(strip_tags($contenu, '<img>')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['source_html' => 'Le modèle est vide.']);
        }
        return [
            'nom' => $data['nom'], 'source_html' => $data['source_html'], 'source_css' => $data['source_css'] ?? null,
            'contenu' => $contenu, 'actif' => (bool) ($data['actif'] ?? false), 'verrouille' => (bool) ($data['verrouille'] ?? false),
        ];
    }

    private static function exempleCss(): string
    {
        return <<<'CSS'
.sig { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #334155; }
.sig-texte { padding-left: 12px; border-left: 3px solid #4F46E5; }
.sig-nom { font-size: 15px; font-weight: 700; color: #0F172A; margin: 0; }
.sig-poste { color: #4F46E5; margin: 0 0 4px; }
.sig-entreprise { font-weight: 700; margin: 0; }
.sig-contact { font-size: 12px; color: #64748B; margin: 2px 0 0; }
CSS;
    }

    /** Modèle de départ : mise en page en tableau, compatible Outlook. */
    private static function exemple(): string
    {
        return <<<'HTML'
<table cellpadding="0" cellspacing="0" role="presentation" class="sig">
  <tr>
    <td class="sig-texte">
      <p class="sig-nom">{{nom_complet}}</p>
      <p class="sig-poste">{{poste}}</p>
      <p class="sig-entreprise">{{entreprise}}</p>
      <p class="sig-contact">Tél. {{telephone}} · <a href="mailto:{{email}}">{{email}}</a></p>
    </td>
  </tr>
</table>
HTML;
    }
}
