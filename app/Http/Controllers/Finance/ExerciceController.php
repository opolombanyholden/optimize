<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Ligne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExerciceController extends Controller
{
    public function index(Request $request)
    {
        $exercices = Exercice::query()
            ->withCount('budgetLignes')
            ->when($request->search, fn($q, $s) => $q->where('libelle', 'like', "%{$s}%"))
            ->when($request->statut, fn($q, $s) => $q->where('statut', $s))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('finance.exercices.index', compact('exercices'));
    }

    public function create()
    {
        return view('finance.exercices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'exercice'            => 'nullable|string|max:255',
            'libelle'             => 'required|string|max:255',
            'datedebut'           => 'nullable|date',
            'datefin'             => 'nullable|date|after_or_equal:datedebut',
            'budgetglobalinitial' => 'nullable|numeric|min:0',
            'dotationglobale'     => 'nullable|numeric|min:0',
            'fondpropreglobal'    => 'nullable|numeric|min:0',
            'commentaire'         => 'nullable|string',
            'type_planification'  => 'nullable|in:depense,recette,mixte',
        ]);
        $validated['type_planification'] = $validated['type_planification'] ?? Exercice::TYPE_MIXTE;

        // Nouvel exercice = toujours en mode planification (statut=1) et brouillon (validation_statut=0)
        $validated['statut']            = Exercice::STATUT_PLANIFICATION;
        $validated['validation_statut'] = 0;
        $validated['id_user']           = $request->user()->id;

        // Création + matérialisation immédiate de toutes les lignes du référentiel à 0
        [$exercice, $lignesCreees] = DB::transaction(function () use ($validated, $request) {
            $ex = Exercice::create($validated);
            $n  = $ex->initialiserPlanification($request->user()->id);
            return [$ex, $n];
        });

        return redirect()->route('finance.exercices.planification', $exercice)
            ->with('success', "Exercice « {$exercice->libelle} » créé. {$lignesCreees} ligne(s) budgétaire(s) du référentiel matérialisée(s) — renseignez maintenant les montants.");
    }

    public function show(string $id)
    {
        $exercice = Exercice::with(['budgetLignes.ligne.titre', 'user', 'soumetteur', 'validateur', 'cloturePar'])
            ->findOrFail($id);
        return view('finance.exercices.show', compact('exercice'));
    }

    public function edit(string $id)
    {
        $exercice = Exercice::findOrFail($id);
        $isSuperAdmin = auth()->user()?->hasRole('super-admin');
        if (!$exercice->peutEtreModifie() && !$isSuperAdmin) {
            return back()->with('error', "L'exercice n'est plus modifiable (statut : {$exercice->statut_libelle} / {$exercice->validation_libelle}).");
        }
        return view('finance.exercices.edit', compact('exercice'));
    }

    public function update(Request $request, string $id)
    {
        $exercice = Exercice::findOrFail($id);
        $isSuperAdmin = auth()->user()?->hasRole('super-admin');
        if (!$exercice->peutEtreModifie() && !$isSuperAdmin) {
            return back()->with('error', "Modifications interdites : un exercice {$exercice->statut_libelle} ne peut plus être édité.");
        }

        $validated = $request->validate([
            'exercice'               => 'nullable|string|max:255',
            'libelle'                => 'required|string|max:255',
            'datedebut'              => 'nullable|date',
            'datefin'                => 'nullable|date|after_or_equal:datedebut',
            'budgetglobalinitial'    => 'nullable|numeric|min:0',
            'commentaire'            => 'nullable|string',
            'dotationglobale'        => 'nullable|numeric|min:0',
            'fondpropreglobal'       => 'nullable|numeric|min:0',
            'reportbudgetare'        => 'nullable|numeric|min:0',
            'reporttresorerieglobal' => 'nullable|numeric|min:0',
            'type_planification'     => 'nullable|in:depense,recette,mixte',
        ]);
        $ancienType = $exercice->type_planification;
        $exercice->update($validated);

        // Si le périmètre change : 1) matérialise les nouvelles lignes en scope,
        // 2) retire les lignes désormais hors scope (soft, réversible), 3) prévient
        // l'utilisateur si des lignes hors scope ont des engagements → à traiter à la main.
        $messages = [];
        if (($validated['type_planification'] ?? $ancienType) !== $ancienType) {
            $creees = $exercice->initialiserPlanification();
            $recap  = $exercice->reconcilierAvecTypePlanification();
            $bloquees = $recap['bloquees'];

            if ($creees > 0)          $messages[] = "{$creees} nouvelle(s) ligne(s) matérialisée(s) dans le nouveau périmètre";
            if ($recap['retirees'] > 0) $messages[] = "{$recap['retirees']} ligne(s) hors scope retirée(s) automatiquement";
            if ($recap['remises'] > 0)  $messages[] = "{$recap['remises']} ligne(s) précédemment retirée(s) remises car de nouveau en scope";
            if ($bloquees->isNotEmpty()) {
                $codes = $bloquees->pluck('id_codeanalytique')->join(', ');
                return redirect()->route('finance.exercices.show', $exercice)
                    ->with('warning', "Périmètre changé, mais {$bloquees->count()} ligne(s) hors scope ont des engagements (id_codeanalytique : {$codes}). Elles restent visibles — traitez-les manuellement.");
            }
        }

        $flash = 'Exercice mis à jour.' . ($messages ? ' ' . implode(' · ', $messages) . '.' : '');
        return redirect()->route('finance.exercices.show', $exercice)->with('success', $flash);
    }

    public function destroy(string $id)
    {
        $exercice = Exercice::findOrFail($id);
        $isSuperAdmin = auth()->user()?->hasRole('super-admin');

        // Super-admin : bypass total avec purge en cascade des BudgetLignes.
        if ($isSuperAdmin) {
            $libelle = $exercice->libelle;
            DB::transaction(function () use ($exercice) {
                $exercice->budgetLignes()->delete();
                $exercice->delete();
            });
            return redirect()->route('finance.exercices.index')
                ->with('success', "[Super-admin] Exercice « {$libelle} » et toutes ses lignes budgétaires supprimés.");
        }

        // Règles métier (verrouillage strict) — cf. Exercice::peutEtreSupprime()
        if ($exercice->estActifEnExecution()) {
            return back()->with('error', "Un exercice en exécution ne peut pas être supprimé. Utilisez « Clôturer » ou « Annuler ».");
        }
        if ((int) $exercice->statut === Exercice::STATUT_CLOTURE) {
            return back()->with('error', "Un exercice clôturé est archivé et ne peut plus être supprimé.");
        }
        if ((int) $exercice->statut === Exercice::STATUT_ANNULE) {
            return back()->with('error', "Un exercice annulé conserve sa trace d'audit et ne peut plus être supprimé.");
        }
        if (!$exercice->peutEtreSupprime()) {
            if ($exercice->budgetLignes()->exists()) {
                return back()->with('error', "Cet exercice contient des lignes budgétaires. Supprimez-les d'abord.");
            }
            return back()->with('error', "Suppression impossible dans cet état.");
        }

        $exercice->delete();
        return redirect()->route('finance.exercices.index')->with('success', 'Exercice supprimé.');
    }

    /**
     * Synchronise les lignes du référentiel absentes de la planification.
     * Utile si des lignes ont été ajoutées au référentiel après création de l'exercice.
     */
    public function synchroniserReferentiel(string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peut_planifier) {
            return back()->with('error', "Synchronisation impossible : planification verrouillée (statut : {$exercice->validation_libelle}).");
        }
        $n = $exercice->initialiserPlanification();
        return back()->with('success', $n > 0
            ? "{$n} ligne(s) du référentiel ajoutée(s) à la planification."
            : "Le référentiel est déjà à jour dans la planification.");
    }

    // ═════════ WORKFLOW PLANIFICATION ═════════

    /**
     * Espace de planification budgétaire de l'exercice.
     * Affiche TOUTES les lignes du référentiel, regroupées par titre, en format grille.
     * Pré-remplit avec les BudgetLignes existantes le cas échéant.
     */
    public function planification(string $id)
    {
        $exercice = Exercice::findOrFail($id);

        // Charge les titres du référentiel filtrés selon le périmètre de l'exercice
        $titres = \App\Models\Titre::with(['lignes' => function ($q) {
            $q->orderBy('id_codeanalytique');
        }])
            ->whereIn('type_ligne', $exercice->typesLigneAcceptes())
            ->orderBy('imputation')
            ->get();

        // Index des BudgetLignes actives par FK Ligne (stockée dans id_codeanalytique)
        $budgetLignesIndex = $exercice->budgetLignes()
            ->nonRetirees()
            ->whereNotNull('id_codeanalytique')
            ->get()
            ->keyBy('id_codeanalytique');

        // Lignes retirées (matérialisées mais volontairement exclues de la grille)
        $lignesRetirees = $exercice->budgetLignes()
            ->retirees()
            ->whereNotNull('id_codeanalytique')
            ->with('ligne.titre')
            ->orderBy('id_codeanalytique')
            ->get();

        // Lignes budgétaires « libres » (sans référentiel) — non concernées par le retrait
        $lignesLibres = $exercice->budgetLignes()->whereNull('id_codeanalytique')->get();

        return view('finance.exercices.planification', compact(
            'exercice', 'titres', 'budgetLignesIndex', 'lignesLibres', 'lignesRetirees'
        ));
    }

    /**
     * Retire une ligne budgétaire de la planification (soft — préserve historique).
     */
    public function retirerLigne(string $exerciceId, string $budgetLigneId)
    {
        $exercice = Exercice::findOrFail($exerciceId);
        $isSuperAdmin = auth()->user()?->hasRole('super-admin');
        if (!$exercice->peut_planifier && !$isSuperAdmin) {
            return back()->with('error', "Retrait impossible : planification verrouillée.");
        }
        $bl = BudgetLigne::where('id', $budgetLigneId)
            ->where('id_exercicebudgetaire', $exercice->id)
            ->firstOrFail();
        if (!$bl->peutEtreRetiree() && !$isSuperAdmin) {
            return back()->with('error', "Cette ligne a des engagements et ne peut pas être retirée.");
        }
        $bl->update(['retiree_de_planification' => true]);
        $msg = "Ligne retirée de la planification.";
        if ($isSuperAdmin && (float) ($bl->engagement ?? 0) > 0) {
            $msg = "[Super-admin] Ligne avec engagement retirée. Vérifiez la cohérence comptable.";
        }
        return back()->with('success', $msg);
    }

    /**
     * Remet une ligne retirée dans la planification active.
     */
    public function remettreLigne(string $exerciceId, string $budgetLigneId)
    {
        $exercice = Exercice::findOrFail($exerciceId);
        if (!$exercice->peut_planifier) {
            return back()->with('error', "Remise impossible : planification verrouillée.");
        }
        $bl = BudgetLigne::where('id', $budgetLigneId)
            ->where('id_exercicebudgetaire', $exercice->id)
            ->firstOrFail();
        $bl->update(['retiree_de_planification' => false]);
        return back()->with('success', "Ligne remise dans la planification.");
    }

    /**
     * Enregistre les modifications de planification (grille de toutes les lignes du référentiel + lignes libres).
     */
    public function planificationSave(Request $request, string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peut_planifier) {
            return back()->with('error', "Modifications interdites : planification verrouillée (statut : {$exercice->validation_libelle}).");
        }

        $data = $request->validate([
            // Lignes du référentiel : indexées par ligne_id (id_codeanalytique)
            'ref'                          => 'nullable|array',
            'ref.*.dotation_etat'          => 'nullable|numeric|min:0',
            'ref.*.fonds_propres'          => 'nullable|numeric|min:0',
            'ref.*.reports_budgetaire'     => 'nullable|numeric|min:0',
            'ref.*.reports_tresorerie'     => 'nullable|numeric|min:0',
            'ref.*.commentaire'            => 'nullable|string|max:1000',
            // Lignes libres (héritées / sans code analytique)
            'libres'                       => 'nullable|array',
            'libres.*.id'                  => 'nullable|exists:budget_lignes,id',
            'libres.*.id_budgetligne'      => 'nullable|string|max:255',
            'libres.*.commentaire'         => 'nullable|string|max:1000',
            'libres.*.dotation_etat'       => 'nullable|numeric|min:0',
            'libres.*.fonds_propres'       => 'nullable|numeric|min:0',
            'libres.*.reports_budgetaire'  => 'nullable|numeric|min:0',
            'libres.*.reports_tresorerie'  => 'nullable|numeric|min:0',
            'libres.*._delete'             => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data, $exercice, $request) {
            $userId = $request->user()->id;

            // ─── 1. Traitement des lignes du référentiel (grille) ───
            // Clé = id_codeanalytique (FK Ligne). On ignore les lignes retirées de la planif.
            $existantes = $exercice->budgetLignes()
                ->nonRetirees()
                ->whereNotNull('id_codeanalytique')
                ->get()
                ->keyBy('id_codeanalytique');

            foreach (($data['ref'] ?? []) as $codeAna => $payload) {
                $codeAnaInt = (int) $codeAna;
                $dotation  = (float) ($payload['dotation_etat'] ?? 0);
                $fp        = (float) ($payload['fonds_propres'] ?? 0);
                $repB      = (float) ($payload['reports_budgetaire'] ?? 0);
                $repT      = (float) ($payload['reports_tresorerie'] ?? 0);
                $total     = $dotation + $fp + $repB + $repT;
                $ligneRef  = Ligne::find($codeAnaInt);
                if (!$ligneRef) continue;

                $existante = $existantes->get($codeAnaInt);

                if ($existante) {
                    // Une ligne du référentiel matérialisée reste toujours présente
                    // (même à zéro) — elle a été créée à l'initialisation de l'exercice.
                    $existante->update([
                        'commentaire'         => $payload['commentaire'] ?? $existante->commentaire,
                        'dotation_etat'       => $dotation,
                        'fonds_propres'       => $fp,
                        'reports_budgetaire'  => $repB,
                        'reports_tresorerie'  => $repT,
                    ]);
                } elseif ($total > 0) {
                    // Ligne du référentiel ajoutée après création (rare) : on la crée si renseignée
                    BudgetLigne::create([
                        'id_exercicebudgetaire'    => $exercice->id,
                        'exercice'                 => $exercice->exercice,
                        'id_budgetligne'           => sprintf('BL-%s-%d', $exercice->exercice ?: $exercice->id, $codeAnaInt),
                        'id_codeanalytique'        => $codeAnaInt,
                        'id_famillecodeanalytique' => $ligneRef->id_titre,
                        'commentaire'              => $payload['commentaire'] ?? $ligneRef->libelle,
                        'dotation_etat'            => $dotation,
                        'fonds_propres'            => $fp,
                        'reports_budgetaire'       => $repB,
                        'reports_tresorerie'       => $repT,
                        'isvalide'                 => 0,
                        'id_user'                  => $userId,
                    ]);
                }
            }

            // ─── 2. Traitement des lignes libres (sans code analytique) ───
            foreach (($data['libres'] ?? []) as $payload) {
                if (!empty($payload['_delete']) && !empty($payload['id'])) {
                    $line = BudgetLigne::find($payload['id']);
                    if ($line && $line->id_exercicebudgetaire == $exercice->id && (float) $line->engagement <= 0) {
                        $line->delete();
                    }
                    continue;
                }

                if (!empty($payload['id'])) {
                    $line = BudgetLigne::find($payload['id']);
                    if ($line && $line->id_exercicebudgetaire == $exercice->id) {
                        $line->update([
                            'id_budgetligne'         => $payload['id_budgetligne'] ?? $line->id_budgetligne,
                            'commentaire'            => $payload['commentaire'] ?? $line->commentaire,
                            'dotation_etat'          => (float) ($payload['dotation_etat'] ?? 0),
                            'fonds_propres'          => (float) ($payload['fonds_propres'] ?? 0),
                            'reports_budgetaire'     => (float) ($payload['reports_budgetaire'] ?? 0),
                            'reports_tresorerie'     => (float) ($payload['reports_tresorerie'] ?? 0),
                        ]);
                    }
                    continue;
                }

                // Nouvelle ligne libre
                if (!empty($payload['id_budgetligne'])) {
                    BudgetLigne::create([
                        'id_exercicebudgetaire'    => $exercice->id,
                        'exercice'                 => $exercice->exercice,
                        'id_budgetligne'           => $payload['id_budgetligne'],
                        'commentaire'              => $payload['commentaire'] ?? null,
                        'dotation_etat'            => (float) ($payload['dotation_etat'] ?? 0),
                        'fonds_propres'            => (float) ($payload['fonds_propres'] ?? 0),
                        'reports_budgetaire'       => (float) ($payload['reports_budgetaire'] ?? 0),
                        'reports_tresorerie'       => (float) ($payload['reports_tresorerie'] ?? 0),
                        'isvalide'                 => 0,
                        'id_user'                  => $userId,
                    ]);
                }
            }
        });

        return redirect()->route('finance.exercices.planification', $exercice)
            ->with('success', 'Planification budgétaire enregistrée.');
    }

    /**
     * Soumet la planification pour validation du top management.
     */
    public function soumettre(string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peut_soumettre) {
            return back()->with('error', 'Cet exercice ne peut pas être soumis (état actuel ou aucune ligne budgétaire).');
        }
        $exercice->update([
            'validation_statut' => 1,
            'soumis_par'        => auth()->id(),
            'soumis_at'         => now(),
            'motif_rejet'       => null,
        ]);
        return back()->with('success', 'Planification soumise au top management pour validation.');
    }

    /**
     * Le top management valide la planification : passe l'exercice en exécution.
     */
    public function valider(string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peut_valider) {
            return back()->with('error', 'Aucune planification soumise à valider.');
        }
        // Garantit qu'il n'y a qu'un seul exercice en cours
        $conflit = Exercice::enCours()->where('id', '!=', $exercice->id)->exists();
        if ($conflit) {
            return back()->with('error', 'Un autre exercice est déjà en exécution. Clôturez-le avant.');
        }
        DB::transaction(function () use ($exercice) {
            $exercice->update([
                'statut'            => 2, // exécution
                'validation_statut' => 2, // validé
                'valide_par'        => auth()->id(),
                'valide_at'         => now(),
            ]);
            // Valide automatiquement toutes les lignes budgétaires (isvalide=1)
            BudgetLigne::where('id_exercicebudgetaire', $exercice->id)->update(['isvalide' => 1]);
        });
        return back()->with('success', "Planification validée. L'exercice « {$exercice->libelle} » est en EXÉCUTION.");
    }

    /**
     * Le top management rejette la planification avec motif. Retour en planification (validation_statut=0).
     */
    public function rejeter(Request $request, string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peut_rejeter) {
            return back()->with('error', 'Aucune planification soumise à rejeter.');
        }
        $data = $request->validate(['motif_rejet' => 'required|string|max:500']);
        $exercice->update([
            'validation_statut' => 0, // retour en planification
            'motif_rejet'       => $data['motif_rejet'],
            'valide_par'        => auth()->id(), // qui a rejeté
            'valide_at'         => now(),
        ]);
        return back()->with('success', 'Planification rejetée. Le créateur peut corriger et resoumettre.');
    }

    /**
     * Clôture l'exercice (statut 2 → 3). Définitif.
     */
    public function cloturer(string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peut_cloturer) {
            return back()->with('error', 'Seul un exercice en exécution peut être clôturé.');
        }
        $exercice->update([
            'statut'      => Exercice::STATUT_CLOTURE,
            'cloture_par' => auth()->id(),
            'cloture_at'  => now(),
        ]);
        return back()->with('success', "Exercice « {$exercice->libelle} » clôturé définitivement.");
    }

    /**
     * Annule l'exercice en cours d'exécution (statut 2 → 4).
     * Nécessite un motif d'annulation pour audit.
     */
    public function annuler(Request $request, string $id)
    {
        $exercice = Exercice::findOrFail($id);
        if (!$exercice->peutEtreAnnule()) {
            return back()->with('error', "Seul un exercice en exécution peut être annulé (statut actuel : {$exercice->statut_libelle}).");
        }
        $data = $request->validate([
            'motif_annulation' => 'required|string|min:10|max:500',
        ]);
        $exercice->update([
            'statut'           => Exercice::STATUT_ANNULE,
            'annule_par'       => auth()->id(),
            'annule_at'        => now(),
            'motif_annulation' => $data['motif_annulation'],
        ]);
        return back()->with('success', "Exercice « {$exercice->libelle} » annulé. Motif consigné pour audit.");
    }
}
