<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\OrdreModele;
use App\Models\Finance\OrdreModeleChamp;
use App\Models\Finance\OrdreModeleSignataire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Administration des modèles d'ordre (dépense/recette) : templates
 * paramétrables qui définissent la présentation des documents et
 * les champs à afficher dans les formulaires.
 */
class OrdreModeleController extends Controller
{
    public function index()
    {
        $modeles = OrdreModele::withCount(['champs', 'signataires', 'ordres'])
            ->orderBy('sens')
            ->orderBy('libelle')
            ->get();

        return view('finance.ordres.modeles.index', compact('modeles'));
    }

    public function create()
    {
        return view('finance.ordres.modeles.create', [
            'modele' => new OrdreModele(['sens' => 'depense', 'actif' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateModele($request);
        $data['avec_mode_reglement'] = $request->boolean('avec_mode_reglement');
        $data['avec_pieces_justif']  = $request->boolean('avec_pieces_justif');
        $data['actif']               = $request->boolean('actif', true);

        $modele = OrdreModele::create($data);

        return redirect()->route('finance.referentiels.ordres-modeles.edit', $modele)
            ->with('success', 'Modèle créé. Ajoutez maintenant les champs et signataires.');
    }

    public function edit(OrdreModele $ordres_modele)
    {
        $ordres_modele->load(['champs', 'signataires.userParDefaut']);

        return view('finance.ordres.modeles.edit', [
            'modele' => $ordres_modele,
            'users'  => User::actif()->orderBy('name')->get(['id', 'name', 'prenoms']),
            'typesSaisie'      => OrdreModeleChamp::TYPES_SAISIE,
            'champsGlDispo'    => OrdreModeleChamp::CHAMPS_GL_DISPONIBLES,
        ]);
    }

    public function update(Request $request, OrdreModele $ordres_modele)
    {
        $data = $this->validateModele($request, $ordres_modele->id);
        $data['avec_mode_reglement'] = $request->boolean('avec_mode_reglement');
        $data['avec_pieces_justif']  = $request->boolean('avec_pieces_justif');
        $data['actif']               = $request->boolean('actif', true);

        $ordres_modele->update($data);

        return redirect()->route('finance.referentiels.ordres-modeles.edit', $ordres_modele)
            ->with('success', 'Modèle mis à jour.');
    }

    public function destroy(OrdreModele $ordres_modele)
    {
        if ($ordres_modele->ordres()->exists()) {
            return back()->with('error', 'Ce modèle est utilisé par des ordres existants. Il ne peut pas être supprimé (désactivez-le si besoin).');
        }
        $ordres_modele->delete();
        return redirect()->route('finance.referentiels.ordres-modeles.index')
            ->with('success', 'Modèle supprimé.');
    }

    // ─── Gestion des champs ─────────────────────────────

    public function storeChamp(Request $request, OrdreModele $ordres_modele)
    {
        $data = $this->validateChamp($request, $ordres_modele->id);
        $data['obligatoire'] = $request->boolean('obligatoire');
        if ($data['type_saisie'] === 'select' && !empty($data['options_texte'] ?? '')) {
            $data['options_json'] = $this->parseOptionsTexte($data['options_texte']);
        }
        unset($data['options_texte']);

        $ordres_modele->champs()->create($data);
        return back()->with('success', 'Champ ajouté.');
    }

    public function updateChamp(Request $request, OrdreModele $ordres_modele, OrdreModeleChamp $champ)
    {
        abort_unless($champ->modele_id === $ordres_modele->id, 404);
        $data = $this->validateChamp($request, $ordres_modele->id, $champ->id);
        $data['obligatoire'] = $request->boolean('obligatoire');
        if ($data['type_saisie'] === 'select' && !empty($data['options_texte'] ?? '')) {
            $data['options_json'] = $this->parseOptionsTexte($data['options_texte']);
        }
        unset($data['options_texte']);
        $champ->update($data);
        return back()->with('success', 'Champ mis à jour.');
    }

    public function destroyChamp(OrdreModele $ordres_modele, OrdreModeleChamp $champ)
    {
        abort_unless($champ->modele_id === $ordres_modele->id, 404);
        $champ->delete();
        return back()->with('success', 'Champ supprimé.');
    }

    public function reordonnerChamps(Request $request, OrdreModele $ordres_modele)
    {
        $ordre = $request->input('ordre', []);
        DB::transaction(function () use ($ordre, $ordres_modele) {
            foreach ($ordre as $position => $id) {
                OrdreModeleChamp::where('id', $id)
                    ->where('modele_id', $ordres_modele->id)
                    ->update(['ordre' => ($position + 1) * 10]);
            }
        });
        return response()->json(['ok' => true]);
    }

    // ─── Gestion des signataires ────────────────────────

    public function storeSignataire(Request $request, OrdreModele $ordres_modele)
    {
        $data = $request->validate([
            'role_libelle'       => 'required|string|max:255',
            'user_id_par_defaut' => 'nullable|exists:users,id',
            'ordre'              => 'nullable|integer|min:0',
        ]);
        $data['ordre'] = $data['ordre'] ?? (($ordres_modele->signataires()->max('ordre') ?? 0) + 10);
        $ordres_modele->signataires()->create($data);
        return back()->with('success', 'Signataire ajouté.');
    }

    public function destroySignataire(OrdreModele $ordres_modele, OrdreModeleSignataire $signataire)
    {
        abort_unless($signataire->modele_id === $ordres_modele->id, 404);
        $signataire->delete();
        return back()->with('success', 'Signataire retiré.');
    }

    // ─── Helpers ───────────────────────────────────────

    private function validateModele(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'                => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', 'unique:finance_ordre_modeles,code,' . ($ignoreId ?? 'NULL')],
            'libelle'             => 'required|string|max:255',
            'sens'                => 'required|in:depense,recette',
            'numerotation_format' => 'nullable|string|max:255',
            'entete_titre'        => 'nullable|string|max:255',
            'entete_soustitre'    => 'nullable|string|max:255',
            'phrase_intro'        => 'nullable|string',
            'phrase_conclusion'   => 'nullable|string',
        ], [
            'code.regex' => 'Le code doit être en minuscules et ne contenir que lettres, chiffres et underscores.',
        ]);
    }

    private function validateChamp(Request $request, int $modeleId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code_champ'         => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_]+$/', 'unique:finance_ordre_modele_champs,code_champ,' . ($ignoreId ?? 'NULL') . ',id,modele_id,' . $modeleId],
            'label_personnalise' => 'required|string|max:255',
            'type_saisie'        => 'required|in:' . implode(',', array_keys(OrdreModeleChamp::TYPES_SAISIE)),
            'mapping_gl'         => 'nullable|in:' . implode(',', array_keys(OrdreModeleChamp::CHAMPS_GL_DISPONIBLES)),
            'largeur_col'        => 'nullable|integer|in:3,4,6,12',
            'ordre'              => 'nullable|integer',
            'placeholder'        => 'nullable|string|max:255',
            'valeur_par_defaut'  => 'nullable|string|max:255',
            'options_texte'      => 'nullable|string',
        ]);
    }

    /**
     * Parse un textarea "code=libellé" par ligne en tableau associatif.
     */
    private function parseOptionsTexte(string $texte): array
    {
        $out = [];
        foreach (preg_split('/\r?\n/', trim($texte)) as $ligne) {
            $ligne = trim($ligne);
            if ($ligne === '') continue;
            [$k, $v] = array_pad(explode('=', $ligne, 2), 2, null);
            $out[trim($k)] = trim($v ?: $k);
        }
        return $out;
    }
}
