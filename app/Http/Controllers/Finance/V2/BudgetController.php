<?php

namespace App\Http\Controllers\Finance\V2;

use App\Http\Controllers\Controller;
use App\Models\Exercice;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetSource;
use App\Models\Finance\Source;
use App\Models\Ligne;
use App\Models\Titre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur Budgets Finance V2.
 * Un budget = 1 enveloppe par (ligne × exercice) avec répartition sur sources (BudgetSource).
 */
class BudgetController extends Controller
{
    public function index(Request $r)
    {
        $exercices = Exercice::orderByDesc('id')->get();
        $exerciceId = $r->exercice_id ?: $exercices->first()?->id;

        $budgets = Budget::with(['ligne.titre'])
            ->when($exerciceId, fn($q) => $q->where('exercice_id', $exerciceId))
            ->orderBy('ligne_id')
            ->get();

        // Récupération des BudgetSources pour cet exercice, indexés par (ligne_id, source_id)
        $sources = Source::orderBy('code')->get();
        $bsMap = BudgetSource::when($exerciceId, fn($q) => $q->where('exercice_id', $exerciceId))
            ->get()
            ->groupBy(fn($bs) => $bs->ligne_id . '_' . $bs->source_id);

        return view('finance.v2.budgets.index', compact('budgets', 'exercices', 'exerciceId', 'sources', 'bsMap'));
    }

    public function planification(Exercice $exercice)
    {
        $titres = Titre::with(['lignes' => fn($q) => $q->orderBy('code')])->orderBy('code')->get();
        $sources = Source::orderBy('code')->get();

        // Index BudgetSource par (ligne_id, source_id)
        $bsMap = BudgetSource::where('exercice_id', $exercice->id)
            ->get()
            ->groupBy(fn($bs) => $bs->ligne_id . '_' . $bs->source_id);

        // Index Budget par ligne_id
        $budgetsByLigne = Budget::where('exercice_id', $exercice->id)->get()->keyBy('ligne_id');

        return view('finance.v2.budgets.planification', compact('exercice', 'titres', 'sources', 'bsMap', 'budgetsByLigne'));
    }

    public function planificationSave(Request $r, Exercice $exercice)
    {
        $data = $r->validate([
            'lignes'                        => 'nullable|array',
            'lignes.*.label'                => 'nullable|string|max:500',
            'lignes.*.seuil'                => 'nullable|numeric|min:0',
            'lignes.*.sources'              => 'nullable|array',
            'lignes.*.sources.*'            => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $exercice) {
            $userId = auth()->id();
            foreach (($data['lignes'] ?? []) as $ligneId => $payload) {
                $ligneId = (int) $ligneId;
                if (!$ligneId) continue;

                $ligne = Ligne::find($ligneId);
                if (!$ligne) continue;

                $totalLigne = 0;
                foreach (($payload['sources'] ?? []) as $sourceId => $montant) {
                    $sourceId = (int) $sourceId;
                    $montant = (float) $montant;
                    if ($sourceId <= 0) continue;

                    if ($montant > 0) {
                        BudgetSource::updateOrCreate(
                            ['source_id' => $sourceId, 'ligne_id' => $ligneId, 'exercice_id' => $exercice->id],
                            ['montant' => $montant]
                        );
                        $totalLigne += $montant;
                    } else {
                        BudgetSource::where('source_id', $sourceId)
                            ->where('ligne_id', $ligneId)
                            ->where('exercice_id', $exercice->id)
                            ->delete();
                    }
                }

                // Budget principal : créé si au moins un montant > 0, sinon supprimé
                if ($totalLigne > 0) {
                    Budget::updateOrCreate(
                        ['ligne_id' => $ligneId, 'exercice_id' => $exercice->id],
                        [
                            'label'       => $payload['label'] ?? $ligne->label ?? $ligne->libelle ?? "Budget {$ligne->code}",
                            'seuil'       => $payload['seuil'] ?? null,
                            'status'      => 0,
                        ]
                    );
                } else {
                    Budget::where('ligne_id', $ligneId)->where('exercice_id', $exercice->id)->delete();
                }
            }
        });

        return redirect()->route('finance.v2.budgets.planification', $exercice)
            ->with('success', 'Planification budgétaire enregistrée.');
    }
}
