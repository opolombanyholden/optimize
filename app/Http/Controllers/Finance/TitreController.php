<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Titre;
use Illuminate\Http\Request;

/**
 * Référentiel — Familles/types de lignes budgétaires (Titres).
 * Spec : cahier des charges Finance §2 « Tableau de gestion des familles/types de lignes ».
 */
class TitreController extends Controller
{
    public function index(Request $request)
    {
        $titres = Titre::query()
            ->withCount('lignes')
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('imputation', 'ilike', "%$s%")))
            ->when($request->type_ligne, fn($q, $t) => $q->where('type_ligne', $t))
            ->orderBy('imputation')
            ->paginate(20)
            ->withQueryString();

        return view('finance.referentiels.titres.index', compact('titres'));
    }

    public function create()  { return view('finance.referentiels.titres.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'imputation'  => 'required|string|max:50',
            'libelle'     => 'required|string|max:255',
            'type_ligne'  => 'nullable|in:depense,recette,mixte',
            'seuil'       => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $data['id_user'] = auth()->id();
        $titre = Titre::create($data);
        return redirect()->route('finance.referentiels.titres.index')->with('success', 'Titre créé.');
    }

    public function show(Titre $titre)
    {
        $titre->load('lignes');
        return view('finance.referentiels.titres.show', compact('titre'));
    }

    public function edit(Titre $titre) { return view('finance.referentiels.titres.edit', compact('titre')); }

    public function update(Request $request, Titre $titre)
    {
        $data = $request->validate([
            'imputation'  => 'required|string|max:50',
            'libelle'     => 'required|string|max:255',
            'type_ligne'  => 'nullable|in:depense,recette,mixte',
            'seuil'       => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);
        $titre->update($data);
        return redirect()->route('finance.referentiels.titres.show', $titre)->with('success', 'Titre mis à jour.');
    }

    public function destroy(Titre $titre)
    {
        if ($titre->lignes()->exists()) {
            return back()->with('error', 'Ce titre contient des lignes. Détachez-les d\'abord.');
        }
        $titre->delete();
        return redirect()->route('finance.referentiels.titres.index')->with('success', 'Titre supprimé.');
    }
}
