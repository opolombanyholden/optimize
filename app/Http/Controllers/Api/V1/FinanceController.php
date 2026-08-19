<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\GrandLivre;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function exercices(Request $request): JsonResponse
    {
        $query = Exercice::query();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('annee')) {
            $query->where('annee', $request->annee);
        }

        $exercices = $query->orderByDesc('annee')->paginate($request->input('per_page', 15));

        return response()->json($exercices);
    }

    public function showExercice(Exercice $exercice): JsonResponse
    {
        $exercice->load('budgetLignes', 'user');

        return response()->json($exercice);
    }

    public function budgets(Request $request): JsonResponse
    {
        $query = BudgetLigne::with('exercice', 'ligne');

        if ($request->filled('exercice_id')) {
            $query->where('exercice_id', $request->exercice_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $budgets = $query->orderByDesc('created_at')->paginate($request->input('per_page', 15));

        return response()->json($budgets);
    }

    public function showBudget(BudgetLigne $budgetLigne): JsonResponse
    {
        $budgetLigne->load('exercice', 'ligne');

        return response()->json($budgetLigne);
    }

    public function comptes(Request $request): JsonResponse
    {
        $query = Compte::query();

        if ($request->filled('type')) {
            $query->where('type_compte', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('libelle', 'like', "%{$request->search}%")
                  ->orWhere('numero_compte', 'like', "%{$request->search}%");
            });
        }

        $comptes = $query->orderBy('numero_compte')->paginate($request->input('per_page', 15));

        return response()->json($comptes);
    }

    public function grandLivre(Request $request): JsonResponse
    {
        $query = GrandLivre::with('exercice');

        if ($request->filled('exercice_id')) {
            $query->where('exercice_id', $request->exercice_id);
        }

        $grandLivres = $query->orderByDesc('created_at')->paginate($request->input('per_page', 15));

        return response()->json($grandLivres);
    }

    public function showGrandLivre(GrandLivre $grandLivre): JsonResponse
    {
        $grandLivre->load('exercice', 'details');

        return response()->json($grandLivre);
    }

    public function transactions(Request $request): JsonResponse
    {
        $query = Transaction::with('exercice');

        if ($request->filled('exercice_id')) {
            $query->where('exercice_id', $request->exercice_id);
        }

        if ($request->filled('type')) {
            $query->where('type_transaction', $request->type);
        }

        $transactions = $query->orderByDesc('date_transaction')->paginate($request->input('per_page', 15));

        return response()->json($transactions);
    }
}
