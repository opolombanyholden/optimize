<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreDetail;
use App\Models\Finance\OrdreModele;
use App\Models\RubriqueOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Création et gestion des ordres de dépense / recette.
 * Le formulaire de saisie est généré dynamiquement à partir du modèle choisi.
 */
class OrdreController extends Controller
{
    public function index(Request $request)
    {
        $ordres = Ordre::with(['modele', 'exercice', 'budgetLigne.ligne', 'createur'])
            ->when($request->filled('sens'),     fn($q) => $q->sens($request->sens))
            ->when($request->filled('statut'), fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->filled('exercice'), fn($q) => $q->exercice((int) $request->exercice))
            ->when($request->filled('modele'),   fn($q) => $q->where('modele_id', (int) $request->modele))
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = $request->input('q');
                $q->where(function ($w) use ($s) {
                    $w->where('numero_ordre', 'like', "%{$s}%")
                      ->orWhere('libelle', 'like', "%{$s}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('finance.ordres.index', [
            'ordres'   => $ordres,
            'modeles'  => OrdreModele::actif()->orderBy('libelle')->get(),
            'exercices'=> Exercice::orderByDesc('id')->get(),
            'statuts'  => Ordre::STATUTS,
        ]);
    }

    /**
     * Étape 1 : sélection du modèle. Si `?modele=` passé, affiche le formulaire dynamique.
     */
    public function create(Request $request)
    {
        $modele = null;
        if ($request->filled('modele')) {
            $modele = OrdreModele::actif()->with(['champs', 'signataires.userParDefaut'])->find($request->modele);
        }

        // Exercices ouverts pour la sélection
        $exercices = Exercice::whereIn('statut', [
            Exercice::STATUT_PLANIFICATION,
            Exercice::STATUT_EN_EXECUTION,
        ])->orderByDesc('id')->get();

        // Pré-remplissage à partir d'une facture (bouton "Nouvel ordre" depuis la fiche facture)
        $ordre = new Ordre(['sens' => $modele?->sens ?? 'depense', 'statut' => Ordre::STATUT_BROUILLON]);
        if ($request->filled('facture_id')) {
            $facture = \App\Models\Facture::find($request->facture_id);
            if ($facture && $modele && $facture->sens === $modele->sens) {
                $ordre->facture_id = $facture->id;
                $ordre->montant    = $facture->solde_restant > 0 ? $facture->solde_restant : $facture->montant_ttc;
                $ordre->libelle    = 'Règlement facture ' . $facture->numero;
            }
        }

        return view('finance.ordres.create', [
            'modele'         => $modele,
            'modelesActifs'  => OrdreModele::actif()->orderBy('sens')->orderBy('libelle')->get(),
            'exercices'      => $exercices,
            'ordre'          => $ordre,
            'usersBenef'     => \App\Models\User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email']),
            'contactsBenef'  => \App\Models\Intranet\Contact::orderBy('nom')->limit(500)->get(['id', 'nom', 'prenoms']),
            'orgsBenef'      => \App\Models\Intranet\ContactOrganisation::orderBy('nom')->limit(500)->get(['id', 'nom', 'raison_sociale', 'type']),
            'comptes'        => \App\Models\Compte::actif()->tresorerie()->orderBy('type')->orderBy('nom')->get(),
            'facturesRattachables' => $modele
                ? \App\Models\Facture::where('sens', $modele->sens)->where('statut', '!=', 3)->orderByDesc('date_emission')->limit(200)->get()
                : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $modele = OrdreModele::actif()->with('champs')->findOrFail($request->input('modele_id'));

        $validated = $this->validateOrdrePayload($request, $modele);

        // Contrôle métier : une facture de dépense doit être ordonnancée avant tout ordre de paiement
        if (!empty($validated['facture_id']) && $modele->sens === 'depense') {
            $facture = \App\Models\Facture::find($validated['facture_id']);
            if ($facture && $facture->sens === 'depense' && !$facture->estOrdonnancee()) {
                return back()->withInput()->with('error', "Facture {$facture->numero} non ordonnancée — l'ordonnateur doit donner son OK avant tout ordre de paiement.");
            }
        }

        $ordre = DB::transaction(function () use ($validated, $modele, $request) {
            $sequence = Ordre::where('modele_id', $modele->id)->count() + 1;
            $numero   = $modele->genererNumero($sequence);

            $bl = BudgetLigne::with('ligne')->find($validated['budget_ligne_id']);

            $donnees = $this->forcerImputationDepuisLigne($validated['donnees'] ?? [], $modele, $bl);
            $benef = $this->normaliserBeneficiaire($validated);

            $ordre = Ordre::create([
                'modele_id'               => $modele->id,
                'numero_ordre'            => $numero,
                'exercice_id'             => $validated['exercice_id'],
                'budget_ligne_id'         => $validated['budget_ligne_id'],
                'compte_id'               => $validated['compte_id'] ?? null,
                'facture_id'              => $validated['facture_id'] ?? null,
                'sens'                    => $modele->sens,
                'libelle'                 => $validated['libelle'] ?? null,
                'montant'                 => 0, // recalculé après détails
                'statut'                  => Ordre::STATUT_BROUILLON,
                'donnees_json'            => $donnees,
                'beneficiaire_source'     => $benef['source'],
                'beneficiaire_ref_id'     => $benef['ref_id'],
                'beneficiaire_infos_json' => $benef['infos'],
                'created_by'              => auth()->id(),
            ]);

            $this->syncDetails($ordre, $request->input('details', []), $bl, $modele);
            $this->attacherJustificatifs($ordre, $request);
            $this->aligneMontantAvecDetails($ordre, $validated);

            return $ordre;
        });

        return redirect()->route('finance.ordres.show', $ordre)
            ->with('success', "Ordre {$ordre->numero_ordre} créé en brouillon.");
    }

    public function show(Ordre $ordre)
    {
        $ordre->load([
            'modele.champs', 'modele.signataires.userParDefaut',
            'exercice', 'budgetLigne.ligne', 'details.rubrique',
            'createur', 'grandLivre', 'piecesJointes',
        ]);
        return view('finance.ordres.show', compact('ordre'));
    }

    public function edit(Ordre $ordre)
    {
        if (!$ordre->peutEtreModifie() && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', "Cet ordre n'est plus modifiable ({$ordre->statut_libelle}).");
        }
        $ordre->load(['modele.champs', 'exercice', 'budgetLigne.ligne', 'details.rubrique']);

        $exercices = Exercice::whereIn('statut', [
            Exercice::STATUT_PLANIFICATION,
            Exercice::STATUT_EN_EXECUTION,
        ])->orderByDesc('id')->get();

        return view('finance.ordres.edit', [
            'ordre'         => $ordre,
            'modele'        => $ordre->modele,
            'exercices'     => $exercices,
            'usersBenef'    => \App\Models\User::actif()->orderBy('name')->get(['id', 'name', 'prenoms', 'email']),
            'contactsBenef' => \App\Models\Intranet\Contact::orderBy('nom')->limit(500)->get(['id', 'nom', 'prenoms']),
            'orgsBenef'     => \App\Models\Intranet\ContactOrganisation::orderBy('nom')->limit(500)->get(['id', 'nom', 'raison_sociale', 'type']),
            'comptes'       => \App\Models\Compte::actif()->tresorerie()->orderBy('type')->orderBy('nom')->get(),
            'facturesRattachables' => \App\Models\Facture::where('sens', $ordre->sens)->where('statut', '!=', 3)->orderByDesc('date_emission')->limit(200)->get(),
        ]);
    }

    public function update(Request $request, Ordre $ordre)
    {
        if (!$ordre->peutEtreModifie() && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', "Modification interdite dans l'état {$ordre->statut_libelle}.");
        }

        $ordre->loadMissing('modele.champs');
        $validated = $this->validateOrdrePayload($request, $ordre->modele);

        DB::transaction(function () use ($validated, $ordre, $request) {
            $bl = BudgetLigne::with('ligne')->find($validated['budget_ligne_id']);
            $donnees = $this->forcerImputationDepuisLigne($validated['donnees'] ?? [], $ordre->modele, $bl);
            $benef = $this->normaliserBeneficiaire($validated);

            $ordre->update([
                'exercice_id'             => $validated['exercice_id'],
                'budget_ligne_id'         => $validated['budget_ligne_id'],
                'compte_id'               => $validated['compte_id'] ?? null,
                'facture_id'              => $validated['facture_id'] ?? null,
                'libelle'                 => $validated['libelle'] ?? null,
                'donnees_json'            => $donnees,
                'beneficiaire_source'     => $benef['source'],
                'beneficiaire_ref_id'     => $benef['ref_id'],
                'beneficiaire_infos_json' => $benef['infos'],
            ]);

            $this->syncDetails($ordre, $request->input('details', []), $bl, $ordre->modele);
            $this->attacherJustificatifs($ordre, $request);
            $this->aligneMontantAvecDetails($ordre, $validated);
        });

        return redirect()->route('finance.ordres.show', $ordre)
            ->with('success', 'Ordre mis à jour.');
    }

    public function destroy(Ordre $ordre)
    {
        $isSuperAdmin = auth()->user()->hasRole('super-admin');
        if ($ordre->statut !== Ordre::STATUT_BROUILLON && !$isSuperAdmin) {
            return back()->with('error', "Seul un ordre en brouillon peut être supprimé. Utilisez « Annuler » pour un ordre soumis/signé.");
        }
        $ordre->delete();
        return redirect()->route('finance.ordres.index')
            ->with('success', $isSuperAdmin && $ordre->statut !== Ordre::STATUT_BROUILLON
                ? "[Super-admin] Ordre {$ordre->numero_ordre} supprimé (état {$ordre->statut_libelle})."
                : "Ordre supprimé.");
    }

    // ═════════ WORKFLOW ═════════

    public function soumettre(Ordre $ordre)
    {
        if (!$ordre->peutEtreSoumis()) {
            return back()->with('error', "Cet ordre ne peut pas être soumis ({$ordre->statut_libelle}).");
        }
        if (!$ordre->budget_ligne_id || !$ordre->montant) {
            return back()->with('error', "L'ordre doit avoir une ligne budgétaire et un montant avant soumission.");
        }
        $ordre->soumettre();
        return back()->with('success', "Ordre {$ordre->numero_ordre} soumis pour signature.");
    }

    public function signer(Ordre $ordre)
    {
        if (!$ordre->peutEtreSigne()) {
            return back()->with('error', "Signature impossible ({$ordre->statut_libelle}).");
        }
        $ordre->signer();
        return back()->with('success', "Ordre {$ordre->numero_ordre} signé. Il peut désormais être exécuté au Grand Livre.");
    }

    public function executer(Ordre $ordre)
    {
        if (!$ordre->peutEtreExecute()) {
            return back()->with('error', "Exécution impossible ({$ordre->statut_libelle}).");
        }
        // Le compte doit être explicitement renseigné pour l'exécution
        if (!$ordre->compte_id) {
            return back()->with('error', "Impossible d'exécuter : aucun compte de trésorerie n'a été renseigné. Éditez l'ordre pour en choisir un.");
        }
        // Vérifie l'engagement disponible pour une dépense
        if ($ordre->sens === 'depense' && $ordre->budgetLigne) {
            $solde = (float) $ordre->budgetLigne->solde_disponible;
            if ($solde < (float) $ordre->montant) {
                return back()->with('error',
                    "Solde disponible insuffisant sur la ligne budgétaire : " .
                    number_format($solde, 0, ',', ' ') . " XAF < " .
                    number_format((float) $ordre->montant, 0, ',', ' ') . " XAF.");
            }
        }

        DB::transaction(fn() => $ordre->executer());

        return back()->with('success',
            "Ordre {$ordre->numero_ordre} exécuté. Écriture Grand Livre #{$ordre->grand_livre_id} créée.");
    }

    public function annuler(Request $request, Ordre $ordre)
    {
        if (!$ordre->peutEtreAnnule()) {
            return back()->with('error', "Annulation impossible ({$ordre->statut_libelle}).");
        }
        $data = $request->validate([
            'motif_annulation' => 'required|string|min:10|max:500',
        ]);
        $ordre->annuler($data['motif_annulation']);
        return back()->with('success', "Ordre {$ordre->numero_ordre} annulé. Motif consigné pour audit.");
    }

    public function pdf(Ordre $ordre)
    {
        $ordre->load([
            'modele.champs', 'modele.signataires.userParDefaut',
            'exercice', 'budgetLigne.ligne', 'details.rubrique',
            'createur',
        ]);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.ordres.pdf', compact('ordre'))
            ->setPaper('A4', 'portrait');
        // Le numero_ordre peut contenir des "/" (format ANPI). Interdits dans un
        // Content-Disposition filename → on remplace par des "-" pour un nom sûr.
        $filename = preg_replace('#[/\\\\]#', '-', $ordre->numero_ordre) . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Endpoint AJAX : liste des budget_lignes actives d'un exercice.
     * Utilisé par le formulaire dynamique quand l'utilisateur choisit l'exercice.
     */
    public function budgetLignesPourExercice(Exercice $exercice)
    {
        $lignes = $exercice->budgetLignes()
            ->nonRetirees()
            ->with('ligne.titre')
            ->orderBy('id_budgetligne')
            ->get()
            ->map(fn($l) => [
                'id'                => $l->id,
                'id_budgetligne'    => $l->id_budgetligne,
                'commentaire'       => $l->commentaire,
                'libelle'           => $l->ligne?->libelle,
                'titre'             => $l->ligne?->titre?->imputation,
                'id_codeanalytique' => $l->ligne?->id_codeanalytique, // code métier ligne = imputation budgétaire
                'dotation_totale'   => (float) $l->budget_total,     // dotation planifiée totale de la ligne
                'solde_disponible'  => (float) $l->solde_disponible, // solde disponible (pour dépenses)
                'cumul_recettes'    => (float) $l->sommeRecettesEnregistrees(), // cumul recettes précédentes (pour recettes)
            ]);
        return response()->json($lignes);
    }

    /**
     * Endpoint AJAX : rubriques disponibles pour une BudgetLigne + sens donné.
     * Sert au formulaire dynamique pour proposer les désignations au moment
     * de la sélection de la ligne budgétaire.
     */
    public function rubriquesPourLigne(BudgetLigne $budgetLigne, string $sens)
    {
        abort_unless(in_array($sens, ['depense', 'recette'], true), 400);
        if (!$budgetLigne->id_codeanalytique) return response()->json([]);

        $rubriques = RubriqueOperation::query()
            ->actif()
            ->where('ligne_id', $budgetLigne->id_codeanalytique)
            ->where(function ($q) use ($sens) {
                $q->where('sens', $sens)->orWhere('sens', 'mixte');
            })
            ->orderBy('ordre_affichage')->orderBy('libelle')
            ->get(['id', 'code', 'libelle']);

        return response()->json($rubriques);
    }

    // ─── Helpers ────────────────────────────────────────

    private function validateOrdrePayload(Request $request, OrdreModele $modele): array
    {
        $rules = [
            'modele_id'       => 'required|exists:finance_ordre_modeles,id',
            'exercice_id'     => [
                'required', 'exists:exercices,id',
                function ($attr, $value, $fail) {
                    $ex = Exercice::find($value);
                    if (!$ex || !in_array((int) $ex->statut, [Exercice::STATUT_PLANIFICATION, Exercice::STATUT_EN_EXECUTION], true)) {
                        $fail("L'exercice doit être en planification ou en exécution.");
                    }
                },
            ],
            'budget_ligne_id' => 'required|exists:budget_lignes,id',
            'compte_id'       => 'nullable|exists:comptes,id',
            'facture_id'      => [
                'nullable', 'exists:factures,id',
                function ($attr, $value, $fail) use ($modele) {
                    if (!$value) return;
                    $f = \App\Models\Facture::find($value);
                    if ($f && $f->sens !== $modele->sens) {
                        $fail("La facture rattachée doit avoir le même sens ({$modele->sens}) que l'ordre.");
                    }
                },
            ],
            'libelle'         => 'nullable|string|max:255',
            'donnees'         => 'nullable|array',
            'details'                   => 'nullable|array',
            'details.*.libelle'         => 'nullable|string|max:255',
            'details.*.rubrique_id'     => 'nullable|exists:rubriques_operations,id',
            'details.*.quantite'        => 'nullable|numeric|min:0',
            'details.*.prix_unitaire'   => 'nullable|numeric|min:0',
            'details.*.observation'     => 'nullable|string',
            // Bénéficiaire
            'beneficiaire_source'     => 'nullable|in:user,contact,organisation,externe',
            'beneficiaire_ref_id'     => 'nullable|integer',
            'beneficiaire_infos'      => 'nullable|array',
            'beneficiaire_infos.nom'         => 'nullable|string|max:255',
            'beneficiaire_infos.entite'      => 'nullable|string|max:255',
            'beneficiaire_infos.telephone'   => 'nullable|string|max:50',
            'beneficiaire_infos.email'       => 'nullable|email|max:255',
            'beneficiaire_infos.adresse'     => 'nullable|string|max:500',
            'beneficiaire_infos.nif'         => 'nullable|string|max:50',
            // Justificatifs (upload)
            'justificatifs'   => 'nullable|array',
            'justificatifs.*' => 'file|max:20480|mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip',
        ];

        // Champs obligatoires du modèle → règle dynamique
        foreach ($modele->champs as $c) {
            if ($c->obligatoire) {
                $rules["donnees.{$c->code_champ}"] = 'required';
            }
        }

        return $request->validate($rules);
    }

    /**
     * Supprime une pièce jointe (justificatif) attachée à un ordre.
     */
    public function destroyJustificatif(Ordre $ordre, \App\Models\Intranet\PieceJointe $piece)
    {
        abort_unless(
            $piece->attachable_id === $ordre->id && $piece->attachable_type === Ordre::class,
            404
        );
        if (!$ordre->peutEtreModifie() && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', "Impossible de modifier les pièces jointes ({$ordre->statut_libelle}).");
        }
        $ordre->detacherFichier($piece->id);
        return back()->with('success', 'Justificatif supprimé.');
    }

    // ─── Helpers bénéficiaire + justificatifs ─────────

    /**
     * Normalise le payload bénéficiaire venant du formulaire :
     *  - source `externe` → uniquement infos JSON, aucune FK
     *  - source `user/contact/organisation` → FK obligatoire, infos = snapshot des données lisibles
     */
    private function normaliserBeneficiaire(array $validated): array
    {
        $source = $validated['beneficiaire_source'] ?? null;
        if (!$source) {
            return ['source' => null, 'ref_id' => null, 'infos' => null];
        }

        if ($source === 'externe') {
            $infos = array_filter($validated['beneficiaire_infos'] ?? [], fn($v) => $v !== null && $v !== '');
            return ['source' => 'externe', 'ref_id' => null, 'infos' => $infos ?: null];
        }

        $refId = (int) ($validated['beneficiaire_ref_id'] ?? 0);
        if (!$refId) {
            return ['source' => null, 'ref_id' => null, 'infos' => null];
        }
        $infos = $this->snapshotBeneficiaire($source, $refId);
        return ['source' => $source, 'ref_id' => $refId, 'infos' => $infos];
    }

    private function snapshotBeneficiaire(string $source, int $refId): ?array
    {
        return match ($source) {
            'user' => (function () use ($refId) {
                $u = \App\Models\User::find($refId);
                return $u ? array_filter([
                    'nom'       => trim(($u->prenoms ?? '') . ' ' . ($u->name ?? '')),
                    'email'     => $u->email,
                    'telephone' => $u->contact ?? null,
                    'entite'    => 'Interne',
                ]) : null;
            })(),
            'contact' => (function () use ($refId) {
                $c = \App\Models\Intranet\Contact::find($refId);
                return $c ? array_filter([
                    'nom'       => $c->nom_complet,
                    'email'     => $c->email,
                    'telephone' => $c->telephone ?: $c->mobile,
                    'adresse'   => $c->adresse,
                    'entite'    => $c->organisation?->nom ?? $c->poste,
                ]) : null;
            })(),
            'organisation' => (function () use ($refId) {
                $o = \App\Models\Intranet\ContactOrganisation::find($refId);
                return $o ? array_filter([
                    'nom'       => $o->nom_affichage,
                    'email'     => $o->email,
                    'telephone' => $o->telephone,
                    'adresse'   => $o->adresse,
                    'nif'       => $o->nif,
                    'entite'    => $o->type_libelle,
                ]) : null;
            })(),
            default => null,
        };
    }

    /**
     * Attache les fichiers uploadés (justificatifs) à un ordre via HasPiecesJointes.
     */
    private function attacherJustificatifs(Ordre $ordre, Request $request): void
    {
        if (!$request->hasFile('justificatifs')) return;
        $ordre->attacherFichiers($request->file('justificatifs'), 'finance/ordres/' . $ordre->id . '/justificatifs');
    }

    /**
     * Aligne le champ `montant` de l'ordre avec la vérité métier :
     *  - Si des détails existent : `montant` = somme des détails, ET si un `montant`
     *    a été saisi côté formulaire (MONTANT EN CHIFFRES), il doit CORRESPONDRE
     *    à cette somme. Sinon, une ValidationException est levée pour rejeter la
     *    sauvegarde et forcer l'utilisateur à réconcilier.
     *  - Si aucun détail : `montant` = valeur saisie côté formulaire.
     *
     * Tolérance de 1 centime pour éviter les rejets liés aux arrondis flottants.
     */
    private function aligneMontantAvecDetails(Ordre $ordre, array $validated): void
    {
        $montantSaisi   = isset($validated['donnees']['montant']) ? (float) $validated['donnees']['montant'] : null;
        $sommeDetails   = (float) $ordre->details()->sum('montant');
        $aDesDetails    = $ordre->details()->count() > 0;

        if ($aDesDetails) {
            if ($montantSaisi !== null && $montantSaisi > 0 && abs($sommeDetails - $montantSaisi) > 0.01) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'donnees.montant' => sprintf(
                        "Le total des rubriques (%s XAF) ne correspond pas au MONTANT EN CHIFFRES saisi (%s XAF). Ajustez l'un ou l'autre avant d'enregistrer.",
                        number_format($sommeDetails, 0, ',', ' '),
                        number_format($montantSaisi, 0, ',', ' ')
                    ),
                ]);
            }
            $ordre->update(['montant' => $sommeDetails]);
        } elseif ($montantSaisi !== null) {
            $ordre->update(['montant' => $montantSaisi]);
        }
    }

    /**
     * Force les valeurs des champs auto-calculés (source de vérité côté serveur) :
     *  - `imputation`        ← id_codeanalytique de la ligne
     *  - `dotation_initiale` ← budget_total de la BL
     *  - `solde_precedent`   ← solde_disponible de la BL avant l'opération
     *  - `nouveau_solde`     ← solde_precedent ± montant (dépense=- / recette=+)
     *
     * Empêche un client de saisir des valeurs incohérentes avec la ligne budgétaire.
     * Détection par convention sur `code_champ` (aligné avec le seeder ANPI).
     */
    private function forcerImputationDepuisLigne(array $donnees, OrdreModele $modele, ?BudgetLigne $bl): array
    {
        if (!$bl) return $donnees;

        $codeAna = $bl->ligne?->id_codeanalytique;
        $dotation = (float) $bl->budget_total;
        // Dépense : solde disponible = budget − engagement
        // Recette : cumul des recettes déjà enregistrées sur la ligne
        $soldePrec = $modele->sens === 'depense'
            ? (float) $bl->solde_disponible
            : (float) $bl->sommeRecettesEnregistrees();
        $montant = (float) ($donnees['montant'] ?? 0);
        $nouveauSolde = $modele->sens === 'depense'
            ? $soldePrec - $montant
            : $soldePrec + $montant;

        foreach ($modele->champs as $c) {
            // Priorité au mapping_gl explicite pour l'imputation
            if ($c->mapping_gl === 'imputation' && $codeAna) {
                $donnees[$c->code_champ] = (string) $codeAna;
                continue;
            }
            // Reconnaissance par code_champ pour les champs auto-calculés
            switch ($c->code_champ) {
                case 'dotation_initiale':
                    $donnees[$c->code_champ] = $dotation;
                    break;
                case 'solde_precedent':
                    $donnees[$c->code_champ] = $soldePrec;
                    break;
                case 'nouveau_solde':
                    $donnees[$c->code_champ] = $nouveauSolde;
                    break;
            }
        }
        return $donnees;
    }

    /**
     * Synchronise les détails d'un ordre à partir d'un payload.
     * Chaque item peut avoir un `id` existant (update) ou en être dépourvu (create).
     * Un flag `_delete=1` supprime la ligne.
     * Filtre les rubriques hors périmètre de la ligne budgétaire ou de mauvais sens.
     */
    private function syncDetails(Ordre $ordre, array $items, ?BudgetLigne $bl, OrdreModele $modele): void
    {
        $rubriquesValides = collect();
        if ($bl && $bl->id_codeanalytique) {
            $rubriquesValides = RubriqueOperation::actif()
                ->where('ligne_id', $bl->id_codeanalytique)
                ->where(function ($q) use ($modele) {
                    $q->where('sens', $modele->sens)->orWhere('sens', 'mixte');
                })
                ->pluck('id');
        }

        $conserves = [];
        foreach ($items as $idx => $item) {
            if (!empty($item['_delete']) && !empty($item['id'])) {
                OrdreDetail::where('id', $item['id'])->where('ordre_id', $ordre->id)->delete();
                continue;
            }
            // Ignore les lignes totalement vides
            $q = (float) ($item['quantite'] ?? 0);
            $pu = (float) ($item['prix_unitaire'] ?? 0);
            $lbl = trim((string) ($item['libelle'] ?? ''));
            if ($lbl === '' && $q <= 0 && $pu <= 0) continue;

            // Sécurité : rubrique doit être dans le périmètre
            $rubId = $item['rubrique_id'] ?? null;
            if ($rubId && $rubriquesValides->isNotEmpty() && !$rubriquesValides->contains((int) $rubId)) {
                $rubId = null; // ignore silencieusement une rubrique hors périmètre
            }

            $payload = [
                'ordre_id'      => $ordre->id,
                'rubrique_id'   => $rubId,
                'libelle'       => $lbl ?: 'Ligne ' . ($idx + 1),
                'quantite'      => $q ?: 1,
                'prix_unitaire' => $pu,
                'observation'   => $item['observation'] ?? null,
                'ordre'         => ($idx + 1) * 10,
            ];

            if (!empty($item['id'])) {
                $d = OrdreDetail::where('id', $item['id'])->where('ordre_id', $ordre->id)->first();
                if ($d) { $d->update($payload); $conserves[] = $d->id; }
            } else {
                $d = OrdreDetail::create($payload);
                $conserves[] = $d->id;
            }
        }
    }
}
