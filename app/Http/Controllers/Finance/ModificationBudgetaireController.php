<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\ModificationBudgetaire;
use App\Services\Finance\ModificationBudgetaireService;
use Illuminate\Http\Request;

/**
 * Workflow de modifications budgétaires (transferts ou apports entre lignes).
 * Spec : cahier des charges Finance §1 (Planification budgétaire / Modifications).
 *
 * Statuts : 0=brouillon, 1=soumise, 2=approuvée, 3=appliquée, 4=rejetée.
 * Transitions :
 *   - brouillon → soumise          (le créateur soumet)
 *   - soumise   → approuvée OU rejetée (validateur)
 *   - approuvée → appliquée        (un opérateur déclenche l'application sur les lignes)
 *   - appliquée → approuvée        (annulation : repasse les soldes en arrière)
 */
class ModificationBudgetaireController extends Controller
{
    public function index(Request $request)
    {
        $modifications = ModificationBudgetaire::query()
            ->with([
                'exercice',
                'ligneSource.ligne.titre', 'ligneSource.titre',
                'ligneDestination.ligne.titre', 'ligneDestination.titre',
                'user', 'soumetteur', 'approbateur',
            ])
            ->when($request->exercice_id, fn($q, $id) => $q->where('exercice_id', $id))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->type, fn($q, $t) => $q->where('type_modification', $t))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $exercices = Exercice::orderByDesc('id')->get();

        return view('finance.modifications-budgetaires.index', compact('modifications', 'exercices'));
    }

    public function create()
    {
        // Seuls les exercices en cours de planification ou d'exécution acceptent
        // une modification budgétaire. Un exercice clôturé/annulé est en lecture seule.
        $exercices = Exercice::whereIn('statut', [
            Exercice::STATUT_PLANIFICATION,
            Exercice::STATUT_EN_EXECUTION,
        ])->orderByDesc('id')->get();

        // Lignes des mêmes exercices — retirées exclues
        $lignes = BudgetLigne::query()
            ->with(['exercice', 'titre', 'ligne.titre'])
            ->nonRetirees()
            ->whereHas('exercice', fn($q) => $q->whereIn('statut', [
                Exercice::STATUT_PLANIFICATION,
                Exercice::STATUT_EN_EXECUTION,
            ]))
            ->orderBy('id_budgetligne')
            ->get();

        return view('finance.modifications-budgetaires.create', compact('exercices', 'lignes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'exercice_id' => [
                'required',
                'exists:exercices,id',
                function ($attr, $value, $fail) {
                    $ex = Exercice::find($value);
                    if (!$ex || !in_array((int) $ex->statut, [Exercice::STATUT_PLANIFICATION, Exercice::STATUT_EN_EXECUTION], true)) {
                        $fail("L'exercice doit être en cours de planification ou d'exécution.");
                    }
                },
            ],
            'type_modification'            => 'required|in:transfert,ajout',
            'objetmodification'            => 'required|string|max:255',
            'montant_modification'         => 'required|numeric|min:0.01',
            'budget_ligne_source_id'       => 'required_if:type_modification,transfert|nullable|exists:budget_lignes,id|different:budget_ligne_destination_id',
            'budget_ligne_destination_id'  => 'required|exists:budget_lignes,id',
            'commentaire'                  => 'nullable|string',
        ], [
            'budget_ligne_source_id.required_if' => 'La ligne source est obligatoire pour un transfert.',
            'budget_ligne_source_id.different'   => 'La ligne source doit être différente de la destination.',
        ]);

        $data['id_user'] = auth()->id();
        $data['statut'] = 0; // brouillon

        $mod = ModificationBudgetaire::create($data);

        return redirect()->route('finance.modifications-budgetaires.show', $mod)
            ->with('success', 'Modification budgétaire créée en brouillon. Vous pouvez maintenant la soumettre pour validation.');
    }

    public function show(ModificationBudgetaire $modifications_budgetaire)
    {
        $modifications_budgetaire->load([
            'exercice',
            'ligneSource.exercice', 'ligneSource.ligne.titre', 'ligneSource.titre',
            'ligneDestination.exercice', 'ligneDestination.ligne.titre', 'ligneDestination.titre',
            'user', 'soumetteur', 'approbateur', 'applicateur',
        ]);
        return view('finance.modifications-budgetaires.show', ['mod' => $modifications_budgetaire]);
    }

    public function edit(ModificationBudgetaire $modifications_budgetaire)
    {
        if (!$modifications_budgetaire->est_modifiable) {
            return back()->with('error', 'Cette modification n\'est plus éditable.');
        }
        // Actifs (planification / exécution) + l'exercice actuel s'il a basculé
        $exercices = Exercice::where(function ($q) use ($modifications_budgetaire) {
            $q->whereIn('statut', [Exercice::STATUT_PLANIFICATION, Exercice::STATUT_EN_EXECUTION]);
            if ($modifications_budgetaire->exercice_id) {
                $q->orWhere('id', $modifications_budgetaire->exercice_id);
            }
        })->orderByDesc('id')->get();

        $lignes = BudgetLigne::with(['exercice', 'titre', 'ligne.titre'])
            ->nonRetirees()
            ->whereHas('exercice', fn($q) => $q->whereIn('statut', [
                Exercice::STATUT_PLANIFICATION,
                Exercice::STATUT_EN_EXECUTION,
            ]))
            ->orderBy('id_budgetligne')
            ->get();
        return view('finance.modifications-budgetaires.edit', [
            'mod' => $modifications_budgetaire, 'exercices' => $exercices, 'lignes' => $lignes,
        ]);
    }

    public function update(Request $request, ModificationBudgetaire $modifications_budgetaire)
    {
        if (!$modifications_budgetaire->est_modifiable) {
            return back()->with('error', 'Modification non éditable.');
        }
        $data = $request->validate([
            'exercice_id' => [
                'required',
                'exists:exercices,id',
                function ($attr, $value, $fail) {
                    $ex = Exercice::find($value);
                    if (!$ex || !in_array((int) $ex->statut, [Exercice::STATUT_PLANIFICATION, Exercice::STATUT_EN_EXECUTION], true)) {
                        $fail("L'exercice doit être en cours de planification ou d'exécution.");
                    }
                },
            ],
            'type_modification'            => 'required|in:transfert,ajout',
            'objetmodification'            => 'required|string|max:255',
            'montant_modification'         => 'required|numeric|min:0.01',
            'budget_ligne_source_id'       => 'required_if:type_modification,transfert|nullable|exists:budget_lignes,id|different:budget_ligne_destination_id',
            'budget_ligne_destination_id'  => 'required|exists:budget_lignes,id',
            'commentaire'                  => 'nullable|string',
        ]);
        // Si on était en statut « rejetée », repasse en brouillon
        if ($modifications_budgetaire->statut === 4) {
            $data['statut'] = 0;
            $data['motif_rejet'] = null;
        }
        $modifications_budgetaire->update($data);
        return redirect()->route('finance.modifications-budgetaires.show', $modifications_budgetaire)
            ->with('success', 'Modification mise à jour.');
    }

    public function destroy(ModificationBudgetaire $modifications_budgetaire)
    {
        if (!$modifications_budgetaire->est_modifiable) {
            return back()->with('error', 'Seules les modifications en brouillon ou rejetées peuvent être supprimées.');
        }
        $modifications_budgetaire->delete();
        return redirect()->route('finance.modifications-budgetaires.index')->with('success', 'Modification supprimée.');
    }

    // ─── Workflow ──────────────────────────────────────

    public function soumettre(ModificationBudgetaire $modifications_budgetaire)
    {
        if (!$modifications_budgetaire->est_soumissible) {
            return back()->with('error', 'Seul un brouillon peut être soumis.');
        }
        $modifications_budgetaire->update([
            'statut'     => 1,
            'soumis_par' => auth()->id(),
            'soumis_at'  => now(),
        ]);
        return back()->with('success', 'Modification soumise pour validation.');
    }

    public function approuver(ModificationBudgetaire $modifications_budgetaire, ModificationBudgetaireService $service)
    {
        if (!$modifications_budgetaire->est_approuvable) {
            return back()->with('error', 'Seule une modification soumise peut être approuvée.');
        }
        // Vérifier d'abord la faisabilité (solde, lignes, etc.)
        $erreurs = $service->verifier($modifications_budgetaire);
        if (!empty($erreurs)) {
            return back()->with('error', 'Modification non approuvable : ' . implode(' ', $erreurs));
        }
        $modifications_budgetaire->update([
            'statut'       => 2,
            'approuve_par' => auth()->id(),
            'approuve_at'  => now(),
        ]);
        return back()->with('success', 'Modification approuvée. Elle peut désormais être appliquée sur les lignes.');
    }

    public function rejeter(Request $request, ModificationBudgetaire $modifications_budgetaire)
    {
        if (!$modifications_budgetaire->est_rejetable) {
            return back()->with('error', 'Seule une modification soumise peut être rejetée.');
        }
        $data = $request->validate([
            'motif_rejet' => 'required|string|max:500',
        ]);
        $modifications_budgetaire->update([
            'statut'      => 4,
            'motif_rejet' => $data['motif_rejet'],
            'approuve_par' => auth()->id(),
            'approuve_at'  => now(),
        ]);
        return back()->with('success', 'Modification rejetée. Le créateur peut la corriger et la resoumettre.');
    }

    public function appliquer(ModificationBudgetaire $modifications_budgetaire, ModificationBudgetaireService $service)
    {
        try {
            $service->appliquer($modifications_budgetaire, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Modification appliquée. Les soldes des lignes ont été ajustés.');
    }

    public function annuler(ModificationBudgetaire $modifications_budgetaire, ModificationBudgetaireService $service)
    {
        try {
            $service->annuler($modifications_budgetaire);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Application annulée. La modification est repassée en "approuvée".');
    }
}
