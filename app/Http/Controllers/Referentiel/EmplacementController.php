<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\Emplacement;
use App\Models\ProduitMouvement;
use App\Models\User;
use Illuminate\Http\Request;

class EmplacementController extends Controller
{
    public function index()
    {
        $racines = Emplacement::whereNull('parent_id')->with('enfants.enfants', 'responsable')->orderBy('ordre')->get();
        return view('referentiel.emplacements.index', [
            'racines' => $racines,
            'toutes' => Emplacement::orderBy('libelle')->get(),
            'types' => Emplacement::TYPES,
            'utilisateurs' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Emplacement $emplacement)
    {
        $emplacement->load(['parent', 'enfants', 'responsable', 'stocks.produit.famille']);
        $mouvements = ProduitMouvement::where(function ($q) use ($emplacement) {
                $q->where('emplacement_id', $emplacement->id)
                  ->orWhere('emplacement_source_id', $emplacement->id);
            })
            ->with(['produit', 'user'])
            ->latest()->limit(20)->get();
        $valeurTotale = $emplacement->stocks->sum(fn($s) => (float) $s->quantite * (float) ($s->produit?->prix_unitaire ?? 0));

        return view('referentiel.emplacements.show', [
            'emplacement' => $emplacement,
            'mouvements' => $mouvements,
            'valeurTotale' => $valeurTotale,
        ]);
    }

    public function store(Request $request)
    {
        Emplacement::create($this->validateData($request) + ['actif' => true]);
        return back()->with('success', 'Emplacement créé.');
    }

    public function update(Request $request, Emplacement $emplacement)
    {
        $data = $this->validateData($request, $emplacement->id);
        if (!empty($data['parent_id']) && (int) $data['parent_id'] === $emplacement->id) {
            return back()->with('error', 'Un emplacement ne peut pas être son propre parent.');
        }
        if (!empty($data['parent_id']) && in_array((int) $data['parent_id'], $emplacement->descendantsIds(), true)) {
            return back()->with('error', 'Impossible d\'affecter un descendant comme parent (cycle).');
        }
        // Case cochée = 1, sinon 0
        $data['actif'] = $request->boolean('actif');
        $emplacement->update($data);
        return back()->with('success', 'Emplacement mis à jour.');
    }

    public function toggle(Emplacement $emplacement)
    {
        $emplacement->update(['actif' => !$emplacement->actif]);
        return back()->with('success', $emplacement->actif ? 'Emplacement activé.' : 'Emplacement désactivé.');
    }

    public function destroy(Emplacement $emplacement)
    {
        if ($emplacement->stocks()->where('quantite', '>', 0)->exists()) {
            return back()->with('error', 'Cet emplacement contient du stock ; videz-le d\'abord.');
        }
        $emplacement->delete();
        return back()->with('success', 'Emplacement supprimé.');
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $uniqueCode = 'unique:emplacements,code' . ($id ? ",{$id}" : '');
        return $request->validate([
            'code' => "required|string|max:30|{$uniqueCode}",
            'libelle' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:emplacements,id',
            'type' => 'nullable|in:magasin,zone,etagere,case,autre',
            'adresse' => 'nullable|string|max:500',
            'responsable_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string|max:1000',
            'ordre' => 'nullable|integer',
            'actif' => 'nullable|boolean',
        ]);
    }
}
