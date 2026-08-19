<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Ligne;
use App\Models\Titre;
use Illuminate\Http\Request;

/**
 * Référentiel — Lignes (codes analytiques) rattachées à un Titre.
 */
class LigneController extends Controller
{
    public function index(Request $request)
    {
        $lignes = Ligne::query()
            ->with('titre')
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('id_codeanalytique', 'ilike', "%$s%")))
            ->when($request->titre_id, fn($q, $id) => $q->where('id_titre', $id))
            ->orderBy('id_codeanalytique')
            ->paginate(20)
            ->withQueryString();

        $titres = Titre::orderBy('imputation')->get();

        return view('finance.referentiels.lignes.index', compact('lignes', 'titres'));
    }

    public function create()
    {
        $titres = Titre::orderBy('imputation')->get();
        return view('finance.referentiels.lignes.create', compact('titres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_titre'                => 'required|exists:titres,id',
            'id_codeanalytique'       => 'required|integer',
            'id_famillecodeanalytique'=> 'nullable|integer',
            'libelle'                 => 'required|string|max:255',
            'nature'                  => 'nullable|string|max:50',
            'seuil'                   => 'nullable|numeric|min:0',
            'description'             => 'nullable|string',
        ]);
        $data['id_user'] = auth()->id();
        $data['nature'] ??= 'fonctionnement'; // valeur par défaut (NOT NULL en DB)
        $ligne = Ligne::create($data);
        return redirect()->route('finance.referentiels.lignes.index')->with('success', 'Ligne créée.');
    }

    public function show(Ligne $ligne)
    {
        $ligne->load('titre');
        $rubriques = \App\Models\RubriqueOperation::where('ligne_id', $ligne->id)->orderBy('ordre_affichage')->get();
        return view('finance.referentiels.lignes.show', compact('ligne', 'rubriques'));
    }

    public function edit(Ligne $ligne)
    {
        $titres = Titre::orderBy('imputation')->get();
        return view('finance.referentiels.lignes.edit', compact('ligne', 'titres'));
    }

    public function update(Request $request, Ligne $ligne)
    {
        $data = $request->validate([
            'id_titre'                => 'required|exists:titres,id',
            'id_codeanalytique'       => 'required|integer',
            'id_famillecodeanalytique'=> 'nullable|integer',
            'libelle'                 => 'required|string|max:255',
            'nature'                  => 'nullable|string|max:50',
            'seuil'                   => 'nullable|numeric|min:0',
            'description'             => 'nullable|string',
        ]);
        $ligne->update($data);
        return redirect()->route('finance.referentiels.lignes.show', $ligne)->with('success', 'Ligne mise à jour.');
    }

    public function destroy(Ligne $ligne)
    {
        if (\App\Models\BudgetLigne::where('id_codeanalytique', $ligne->id)->exists()) {
            return back()->with('error', 'Cette ligne est utilisée dans des budgets. Suppression impossible.');
        }
        $ligne->delete();
        return redirect()->route('finance.referentiels.lignes.index')->with('success', 'Ligne supprimée.');
    }
}
