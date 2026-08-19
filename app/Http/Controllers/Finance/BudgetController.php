<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Titre;
use App\Models\Ligne;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = BudgetLigne::query()
            ->with(['exercice', 'titre', 'ligne', 'user'])
            ->when($request->exercice_id, fn($q, $id) => $q->where('id_exercicebudgetaire', $id))
            ->when($request->search, fn($q, $s) => $q->where('id_budgetligne', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Pour les filtres : liste des exercices disponibles
        $exercices = Exercice::orderByDesc('id')->get();

        return view('finance.budgets.index', compact('budgets', 'exercices'));
    }

    public function create()
    {
        $exercices = Exercice::where('statut', 1)->get();
        $lignes = Ligne::all();

        return view('finance.budgets.create', compact('exercices', 'lignes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_budgetligne' => 'required|string|max:255',
            'id_exercicebudgetaire' => 'required|exists:exercices,id',
            'id_famillecodeanalytique' => 'nullable|exists:titres,id',
            'id_codeanalytique' => 'nullable|exists:lignes,id',
            'codecompte' => 'nullable|string|max:255',
            'budgetligne' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string',
            'dotation_etat' => 'nullable|numeric|min:0',
            'fonds_propres' => 'nullable|numeric|min:0',
            'reports_budgetaire' => 'nullable|numeric|min:0',
            'reports_tresorerie' => 'nullable|numeric|min:0',
        ]);

        // Verrou : exercice doit être en planification (statut=1) ET non soumis
        $exercice = Exercice::find($validated['id_exercicebudgetaire']);
        if (!$exercice || !$exercice->peut_planifier) {
            return back()->with('error', "Création interdite : l'exercice n'est plus en mode planification (ou a été soumis pour validation).")->withInput();
        }

        $validated['id_user'] = $request->user()->id;
        BudgetLigne::create($validated);

        return redirect()->route('finance.budgets.index')->with('success', 'Ligne budgetaire creee avec succes.');
    }

    public function show(string $id)
    {
        $budget = BudgetLigne::with(['exercice', 'titre', 'ligne', 'user'])->findOrFail($id);

        return view('finance.budgets.show', compact('budget'));
    }

    public function edit(string $id)
    {
        $budget = BudgetLigne::findOrFail($id);
        $exercices = Exercice::where('statut', 1)->get();
        $lignes = Ligne::all();

        return view('finance.budgets.edit', compact('budget', 'exercices', 'lignes'));
    }

    public function update(Request $request, string $id)
    {
        $budget = BudgetLigne::findOrFail($id);
        $exercice = Exercice::find($budget->id_exercicebudgetaire);

        // Verrou : pas de modification d'une ligne si l'exercice est en exécution ou clôturé
        if ($exercice && !$exercice->peut_planifier) {
            return back()->with('error', "Modification interdite : l'exercice est en {$exercice->statut_libelle} (planification terminée).");
        }

        $validated = $request->validate([
            'id_budgetligne' => 'sometimes|required|string|max:255',
            'codecompte' => 'nullable|string|max:255',
            'budgetligne' => 'sometimes|numeric|min:0',
            'commentaire' => 'nullable|string',
            'dotation_etat' => 'nullable|numeric|min:0',
            'fonds_propres' => 'nullable|numeric|min:0',
            'reports_budgetaire' => 'nullable|numeric|min:0',
            'reports_tresorerie' => 'nullable|numeric|min:0',
            'transfert' => 'nullable|numeric',
            'engagement' => 'nullable|numeric|min:0',
            'isvalide' => 'nullable|integer|in:0,1',
        ]);

        $budget->update($validated);

        return redirect()->route('finance.budgets.index')->with('success', 'Ligne budgetaire mise a jour avec succes.');
    }

    public function destroy(string $id)
    {
        $budget = BudgetLigne::findOrFail($id);
        if ($budget->isvalide == 1) {
            return back()->with('error', "Impossible de supprimer une ligne validée. Dévalidez-la d'abord.");
        }
        $budget->delete();

        return redirect()->route('finance.budgets.index')->with('success', 'Ligne budgetaire supprimee avec succes.');
    }

    /**
     * Valide une ligne budgétaire (verrouillage pour engagements/écritures).
     * Spec : cahier des charges Finance §2 (Gestion budgétaire — workflow de validation).
     */
    public function valider(string $id)
    {
        $budget = BudgetLigne::findOrFail($id);
        if ($budget->isvalide == 1) {
            return back()->with('error', 'Cette ligne est déjà validée.');
        }
        if ($budget->budget_total <= 0) {
            return back()->with('error', 'Impossible de valider une ligne avec budget nul.');
        }
        $budget->update(['isvalide' => 1]);
        return back()->with('success', 'Ligne budgétaire validée.');
    }

    public function devalider(string $id)
    {
        $budget = BudgetLigne::findOrFail($id);
        if ($budget->isvalide != 1) {
            return back()->with('error', 'Cette ligne n\'est pas validée.');
        }
        if (((float) $budget->engagement) > 0) {
            return back()->with('error', "Impossible de dévalider : cette ligne a des engagements ({$budget->engagement} XAF).");
        }
        $budget->update(['isvalide' => 0]);
        return back()->with('success', 'Ligne budgétaire dévalidée. Modifications possibles à nouveau.');
    }
}
