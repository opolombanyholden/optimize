<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\FamilleArticle;
use App\Models\Produit;
use Illuminate\Http\Request;

class FamilleArticleController extends Controller
{
    public function index(Request $request)
    {
        $racines = FamilleArticle::whereNull('parent_id')
            ->with(['enfants.enfants.enfants'])
            ->withCount([
                'articles',
                'articles as articles_actifs_count' => fn($q) => $q->where('statut', 1),
            ])
            ->orderBy('ordre')->orderBy('libelle')->get();

        return view('referentiel.familles.index', [
            'racines' => $racines,
            'toutes' => FamilleArticle::orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        FamilleArticle::create($this->validateData($request) + ['actif' => true, 'ordre' => (int) $request->input('ordre', 0)]);
        return back()->with('success', 'Famille créée.');
    }

    public function update(Request $request, FamilleArticle $famille)
    {
        $data = $this->validateData($request, $famille->id, true);

        if (!empty($data['parent_id']) && (int) $data['parent_id'] === $famille->id) {
            return back()->with('error', 'Une famille ne peut pas être son propre parent.');
        }
        if (!empty($data['parent_id']) && in_array((int) $data['parent_id'], $famille->descendantsIds(), true)) {
            return back()->with('error', 'Impossible d\'affecter un descendant comme parent (cycle).');
        }
        // Checkbox unchecked = actif absent ⇒ false
        $data['actif'] = $request->boolean('actif');

        $famille->update($data);
        return back()->with('success', 'Famille mise à jour.');
    }

    public function destroy(FamilleArticle $famille)
    {
        // Règle : une famille ne peut pas être supprimée si elle (ou un de ses descendants)
        // contient des produits actifs (statut = 1).
        $descendantsIds = $famille->descendantsIds();
        $nbActifs = Produit::whereIn('famille_id', $descendantsIds)->where('statut', 1)->count();

        if ($nbActifs > 0) {
            return back()->with('error', "Suppression impossible : {$nbActifs} produit(s) actif(s) rattaché(s) à cette famille ou à ses sous-familles. Désactivez-les ou réaffectez-les d'abord.");
        }

        // Sous-familles présentes → refus explicite
        if ($famille->enfants()->exists()) {
            return back()->with('error', 'Cette famille contient des sous-familles ; supprimez-les ou déplacez-les d\'abord.');
        }

        $famille->delete();
        return back()->with('success', 'Famille supprimée.');
    }

    /**
     * Bascule actif / inactif (une famille inactive n'apparaît plus dans les sélecteurs des formulaires produit).
     */
    public function toggle(FamilleArticle $famille)
    {
        $famille->update(['actif' => !$famille->actif]);
        return back()->with('success', $famille->actif ? 'Famille activée.' : 'Famille désactivée.');
    }

    private function validateData(Request $request, ?int $id = null, bool $withActif = false): array
    {
        $uniqueCode = 'unique:familles_articles,code' . ($id ? ",{$id}" : '');
        $rules = [
            'libelle' => 'required|string|max:255',
            'code' => "nullable|string|max:30|{$uniqueCode}",
            'parent_id' => 'nullable|exists:familles_articles,id',
            'description' => 'nullable|string|max:1000',
            'ordre' => 'nullable|integer',
        ];
        return $request->validate($rules);
    }
}
