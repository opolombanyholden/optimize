<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Client;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\OperationFinanciere;
use App\Services\Finance\OperationFinanciereService;
use Illuminate\Http\Request;

class OperationFinanciereController extends Controller
{
    public function index(Request $request)
    {
        $operations = OperationFinanciere::query()
            ->with(['exercice', 'budgetLigne', 'facture', 'createur'])
            ->when($request->type, fn($q, $t) => $q->where('type_operation', $t))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->exercice_id, fn($q, $id) => $q->where('exercice_id', $id))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('numero', 'ilike', "%$s%")->orWhere('objet', 'ilike', "%$s%")))
            ->orderByDesc('date_operation')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $exercices = Exercice::orderByDesc('id')->get();

        return view('finance.operations.index', compact('operations', 'exercices'));
    }

    public function create(Request $request)
    {
        $type = $request->type ?? 'depense';
        $exercices = Exercice::orderByDesc('id')->get();
        $lignes = BudgetLigne::with(['exercice', 'titre'])
            ->whereHas('exercice', fn($q) => $q->whereIn('statut', [1, 2]))
            ->where('isvalide', 1) // seules les lignes validées
            ->orderBy('id_budgetligne')
            ->get();
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $clients = Client::actif()->orderBy('raison_sociale')->get();
        $factures = Facture::whereIn('statut', [1, 2])->orderByDesc('date_emission')->get();
        return view('finance.operations.create', compact('type', 'exercices', 'lignes', 'fournisseurs', 'clients', 'factures'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_operation'      => 'required|in:depense,recette',
            'exercice_id'         => 'nullable|exists:exercices,id',
            'budget_ligne_id'     => 'required_if:type_operation,depense|nullable|exists:budget_lignes,id',
            'tiers_type'          => 'nullable|in:fournisseur,client',
            'tiers_id'            => 'nullable|integer',
            'facture_id'          => 'nullable|exists:factures,id',
            'date_operation'      => 'required|date',
            'objet'               => 'required|string|max:500',
            'montant'             => 'required|numeric|min:0.01',
            'mode_reglement'      => 'nullable|in:virement,cheque,especes,mobile_money',
            'reference_reglement' => 'nullable|string|max:100',
            'commentaire'         => 'nullable|string',
        ]);

        $data['numero'] = OperationFinanciereService::genererNumero($data['type_operation']);
        $data['statut'] = 0;
        $data['created_by'] = auth()->id();

        $op = OperationFinanciere::create($data);

        // Synchroniser les détails si fournis
        $details = $request->input('details', []);
        if (!empty($details) && is_array($details)) {
            app(OperationFinanciereService::class)->syncDetails($op, $details);
        }

        return redirect()->route('finance.operations.show', $op)->with('success', "Ordre {$op->numero} créé en brouillon.");
    }

    public function show(OperationFinanciere $operation)
    {
        $operation->load(['exercice', 'budgetLigne', 'facture', 'createur', 'soumetteur', 'approbateur', 'executeur', 'details.rubrique']);
        $tiers = $operation->tiersResolu();
        return view('finance.operations.show', compact('operation', 'tiers'));
    }

    public function edit(OperationFinanciere $operation)
    {
        if (!$operation->est_modifiable) {
            return back()->with('error', 'Cette opération n\'est plus éditable.');
        }
        $exercices = Exercice::orderByDesc('id')->get();
        $lignes = BudgetLigne::with(['exercice', 'titre'])
            ->whereHas('exercice', fn($q) => $q->whereIn('statut', [1, 2]))
            ->where('isvalide', 1)->get();
        $fournisseurs = Fournisseur::orderBy('nom')->get();
        $clients = Client::actif()->orderBy('raison_sociale')->get();
        $factures = Facture::whereIn('statut', [1, 2])->get();
        return view('finance.operations.edit', compact('operation', 'exercices', 'lignes', 'fournisseurs', 'clients', 'factures'));
    }

    public function update(Request $request, OperationFinanciere $operation)
    {
        if (!$operation->est_modifiable) {
            return back()->with('error', 'Opération non modifiable.');
        }
        $data = $request->validate([
            'exercice_id'         => 'nullable|exists:exercices,id',
            'budget_ligne_id'     => 'nullable|exists:budget_lignes,id',
            'tiers_type'          => 'nullable|in:fournisseur,client',
            'tiers_id'            => 'nullable|integer',
            'facture_id'          => 'nullable|exists:factures,id',
            'date_operation'      => 'required|date',
            'objet'               => 'required|string|max:500',
            'montant'             => 'required|numeric|min:0.01',
            'mode_reglement'      => 'nullable|in:virement,cheque,especes,mobile_money',
            'reference_reglement' => 'nullable|string|max:100',
            'commentaire'         => 'nullable|string',
        ]);
        if ($operation->statut === 4) {
            $data['statut'] = 0;
            $data['motif_rejet'] = null;
        }
        $operation->update($data);

        // Synchroniser les détails
        $details = $request->input('details', []);
        if (is_array($details)) {
            app(OperationFinanciereService::class)->syncDetails($operation, $details);
        }

        return redirect()->route('finance.operations.show', $operation)->with('success', 'Opération mise à jour.');
    }

    public function destroy(OperationFinanciere $operation)
    {
        if (!$operation->est_modifiable) {
            return back()->with('error', 'Seule une opération en brouillon ou rejetée peut être supprimée.');
        }
        $operation->delete();
        return redirect()->route('finance.operations.index')->with('success', 'Opération supprimée.');
    }

    // ─── Workflow ──────────────────────────────────────

    public function soumettre(OperationFinanciere $operation)
    {
        if (!$operation->est_soumissible) {
            return back()->with('error', 'Seul un brouillon peut être soumis.');
        }
        $operation->update([
            'statut'     => 1,
            'soumis_par' => auth()->id(),
            'soumis_at'  => now(),
        ]);
        return back()->with('success', 'Opération soumise pour validation.');
    }

    public function approuver(OperationFinanciere $operation, OperationFinanciereService $service)
    {
        if (!$operation->est_approuvable) {
            return back()->with('error', 'Seule une opération soumise peut être approuvée.');
        }
        $erreurs = $service->verifier($operation);
        if (!empty($erreurs)) {
            return back()->with('error', 'Non approuvable : ' . implode(' ', $erreurs));
        }
        $operation->update([
            'statut'       => 2,
            'approuve_par' => auth()->id(),
            'approuve_at'  => now(),
        ]);
        return back()->with('success', 'Opération approuvée. Elle peut désormais être exécutée.');
    }

    public function rejeter(Request $request, OperationFinanciere $operation)
    {
        if (!$operation->est_rejetable) {
            return back()->with('error', 'Seule une opération soumise peut être rejetée.');
        }
        $data = $request->validate(['motif_rejet' => 'required|string|max:500']);
        $operation->update([
            'statut'       => 4,
            'motif_rejet'  => $data['motif_rejet'],
            'approuve_par' => auth()->id(),
            'approuve_at'  => now(),
        ]);
        return back()->with('success', 'Opération rejetée. Le créateur peut la corriger.');
    }

    public function executer(OperationFinanciere $operation, OperationFinanciereService $service)
    {
        try {
            $service->executer($operation, auth()->id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Opération exécutée. Engagement et écritures comptables passés.');
    }

    public function annuler(OperationFinanciere $operation, OperationFinanciereService $service)
    {
        try {
            $service->annuler($operation);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Opération annulée. Engagement libéré, écritures supprimées.');
    }
}
