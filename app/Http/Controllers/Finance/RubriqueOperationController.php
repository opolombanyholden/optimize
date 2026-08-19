<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Ligne;
use App\Models\RubriqueOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Référentiel — Rubriques d'opérations financières.
 *
 * Chaque rubrique est rattachée à une Ligne analytique (référentiel `lignes`).
 * Lors de la saisie d'une opération financière, on filtre les rubriques disponibles
 * en fonction de la BudgetLigne sélectionnée (via budget_ligne.id_codeanalytique = ligne_id).
 */
class RubriqueOperationController extends Controller
{
    public function index(Request $request)
    {
        $rubriques = RubriqueOperation::query()
            ->with('ligne.titre')
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('libelle', 'ilike', "%$s%")->orWhere('code', 'ilike', "%$s%")))
            ->when($request->sens, fn($q, $s) => $q->where('sens', $s))
            ->when($request->ligne_id, fn($q, $id) => $q->where('ligne_id', $id))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderBy('ligne_id')
            ->orderBy('ordre_affichage')
            ->paginate(20)
            ->withQueryString();

        $lignes = Ligne::orderBy('id_codeanalytique')->get();

        return view('finance.referentiels.rubriques.index', compact('rubriques', 'lignes'));
    }

    public function create()
    {
        $lignes = Ligne::with('titre')->orderBy('id_codeanalytique')->get();
        return view('finance.referentiels.rubriques.create', compact('lignes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50|unique:rubriques_operations,code',
            'libelle'         => 'required|string|max:255',
            'description'     => 'nullable|string',
            'ligne_id'        => 'required|exists:lignes,id',
            'sens'            => 'required|in:depense,recette,mixte',
            'categorie'       => 'nullable|string|max:50',
            'ordre_affichage' => 'nullable|integer|min:0',
            'statut'          => 'nullable|in:0,1',
        ]);
        $data['created_by'] = auth()->id();
        $data['statut'] ??= 1;

        $rub = RubriqueOperation::create($data);
        return redirect()->route('finance.referentiels.rubriques.index')->with('success', "Rubrique « {$rub->libelle} » créée.");
    }

    public function show(RubriqueOperation $rubrique)
    {
        $rubrique->load('ligne.titre');
        return view('finance.referentiels.rubriques.show', compact('rubrique'));
    }

    public function edit(RubriqueOperation $rubrique)
    {
        $lignes = Ligne::with('titre')->orderBy('id_codeanalytique')->get();
        return view('finance.referentiels.rubriques.edit', compact('rubrique', 'lignes'));
    }

    public function update(Request $request, RubriqueOperation $rubrique)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50|unique:rubriques_operations,code,' . $rubrique->id,
            'libelle'         => 'required|string|max:255',
            'description'     => 'nullable|string',
            'ligne_id'        => 'required|exists:lignes,id',
            'sens'            => 'required|in:depense,recette,mixte',
            'categorie'       => 'nullable|string|max:50',
            'ordre_affichage' => 'nullable|integer|min:0',
            'statut'          => 'nullable|in:0,1',
        ]);
        $rubrique->update($data);
        return redirect()->route('finance.referentiels.rubriques.show', $rubrique)->with('success', 'Rubrique mise à jour.');
    }

    public function destroy(RubriqueOperation $rubrique)
    {
        if ($rubrique->details()->exists()) {
            return back()->with('error', 'Cette rubrique est utilisée dans des opérations. Archivez-la plutôt (statut=0).');
        }
        $rubrique->delete();
        return redirect()->route('finance.referentiels.rubriques.index')->with('success', 'Rubrique supprimée.');
    }

    /**
     * Endpoint AJAX : renvoie les rubriques disponibles pour une BudgetLigne donnée.
     * Filtre par budget_ligne.id_codeanalytique = rubrique.ligne_id et sens compatible.
     */
    public function parBudgetLigne(BudgetLigne $budgetLigne, Request $request): JsonResponse
    {
        $sens = $request->sens ?? 'mixte';
        $ligneId = $budgetLigne->id_codeanalytique;

        // Si la BudgetLigne n'a pas de Ligne associée, on ne peut pas filtrer → renvoie liste vide.
        if (!$ligneId) {
            return response()->json(['rubriques' => [], 'message' => 'Cette ligne budgétaire n\'est pas rattachée à un code analytique. Aucune rubrique filtrée.']);
        }

        $rubriques = RubriqueOperation::actif()
            ->where('ligne_id', $ligneId)
            ->when($sens === 'depense', fn($q) => $q->whereIn('sens', ['depense', 'mixte']))
            ->when($sens === 'recette', fn($q) => $q->whereIn('sens', ['recette', 'mixte']))
            ->orderBy('ordre_affichage')
            ->orderBy('libelle')
            ->get(['id', 'code', 'libelle', 'sens', 'categorie']);

        return response()->json([
            'rubriques' => $rubriques,
            'ligne_id'  => $ligneId,
        ]);
    }
}
