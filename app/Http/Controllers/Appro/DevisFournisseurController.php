<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CommandeFournisseur;
use App\Models\DevisFournisseur;
use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevisFournisseurController extends Controller
{
    public function index(Request $request)
    {
        $devis = DevisFournisseur::with(['commande.fournisseur', 'fournisseur', 'decideur'])
            ->when($request->q, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('numero', 'ilike', "%{$s}%");
            }))
            ->when($request->commande, fn($q, $c) => $q->where('commande_fournisseur_id', $c))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderByDesc('date_reception')
            ->paginate(20)
            ->withQueryString();

        return view('appro.devis-fournisseur.index', [
            'devis' => $devis,
            'statuts' => DevisFournisseur::STATUTS,
        ]);
    }

    public function create(Request $request)
    {
        $commande = null;
        if ($request->filled('commande_id')) {
            $commande = CommandeFournisseur::with('lignes')->find($request->commande_id);
        }
        return view('appro.devis-fournisseur.create', [
            'devis' => new DevisFournisseur([
                'date_reception' => now()->toDateString(),
                'commande_fournisseur_id' => $commande?->id,
            ]),
            'commande' => $commande,
            'commandes' => $commande ? collect() : CommandeFournisseur::whereIn('statut', [
                CommandeFournisseur::STATUT_BROUILLON,
                CommandeFournisseur::STATUT_SOUMISE,
                CommandeFournisseur::STATUT_APPROUVEE,
            ])->orderByDesc('date_commande')->get(),
            'fournisseurs' => ContactOrganisation::where('type', 'fournisseur')->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, null, true);
        $devis = DB::transaction(function () use ($data, $request) {
            $ht  = (float) $data['montant_ht'];
            $ttc = (float) $data['montant_ttc'];
            $d = DevisFournisseur::create([
                'numero' => DevisFournisseur::genererNumero(),
                'commande_fournisseur_id' => $data['commande_fournisseur_id'],
                'fournisseur_id' => $data['fournisseur_id'] ?? null,
                'date_reception' => $data['date_reception'],
                'date_validite' => $data['date_validite'] ?? null,
                'delai_livraison_jours' => $data['delai_livraison_jours'] ?? null,
                'mode_reglement' => $data['mode_reglement'] ?? null,
                'montant_ht'  => $ht,
                'montant_ttc' => $ttc,
                'montant_tva' => max($ttc - $ht, 0),
                'conditions' => $data['conditions'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'statut' => DevisFournisseur::STATUT_RECU,
            ]);
            $d->attacherFichiers($request->file('pieces_jointes'), 'appro/devis-fournisseur');
            return $d;
        });

        return redirect()->route('appro.devis-fournisseur.show', $devis)->with('success', "Devis {$devis->numero} enregistré.");
    }

    public function show(DevisFournisseur $devis)
    {
        $devis->load(['commande.fournisseur', 'fournisseur', 'lignes', 'decideur', 'facture', 'piecesJointes']);
        return view('appro.devis-fournisseur.show', compact('devis'));
    }

    public function edit(DevisFournisseur $devis)
    {
        if (!$devis->peutEtreSelectionne()) {
            return redirect()->route('appro.devis-fournisseur.show', $devis)->with('error', 'Devis non modifiable (déjà décidé).');
        }
        $devis->load('lignes');
        return view('appro.devis-fournisseur.edit', [
            'devis' => $devis,
            'fournisseurs' => ContactOrganisation::where('type', 'fournisseur')->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, DevisFournisseur $devis)
    {
        if (!$devis->peutEtreSelectionne()) return back()->with('error', 'Devis déjà décidé.');
        $data = $this->validateData($request, $devis->id, false);
        DB::transaction(function () use ($devis, $data, $request) {
            $ht  = (float) $data['montant_ht'];
            $ttc = (float) $data['montant_ttc'];
            $devis->update([
                'fournisseur_id' => $data['fournisseur_id'] ?? null,
                'date_reception' => $data['date_reception'],
                'date_validite' => $data['date_validite'] ?? null,
                'delai_livraison_jours' => $data['delai_livraison_jours'] ?? null,
                'mode_reglement' => $data['mode_reglement'] ?? null,
                'montant_ht'  => $ht,
                'montant_ttc' => $ttc,
                'montant_tva' => max($ttc - $ht, 0),
                'conditions' => $data['conditions'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
            ]);
            if ($request->hasFile('pieces_jointes')) {
                $devis->attacherFichiers($request->file('pieces_jointes'), 'appro/devis-fournisseur');
            }
        });
        return redirect()->route('appro.devis-fournisseur.show', $devis)->with('success', 'Devis mis à jour.');
    }

    public function destroy(DevisFournisseur $devis)
    {
        if ($devis->statut === DevisFournisseur::STATUT_SELECTIONNE) {
            return back()->with('error', 'Impossible de supprimer un devis sélectionné.');
        }
        $devis->delete();
        return redirect()->route('appro.devis-fournisseur.index')->with('success', 'Devis supprimé.');
    }

    // ═════════ Workflow ═════════

    public function selectionner(Request $request, DevisFournisseur $devis)
    {
        if (!$devis->peutEtreSelectionne()) return back()->with('error', 'Devis déjà décidé.');
        $data = $request->validate([
            'motivation' => 'required|string|min:5|max:2000',
            'creer_facture' => 'nullable|boolean',
        ]);
        $devis->selectionner($data['motivation'], $request->user()->id);

        // Si la commande n'avait pas encore de fournisseur, on l'affecte depuis le devis sélectionné
        if ($devis->commande && !$devis->commande->fournisseur_id && $devis->fournisseur_id) {
            $devis->commande->update(['fournisseur_id' => $devis->fournisseur_id]);
        }

        // Optionnellement crée une facture pré-remplie
        if (!empty($data['creer_facture'])) {
            $facture = Facture::create([
                'numero' => 'FA-' . now()->format('Ymd-His'),
                'sens' => 'depense',
                'tiers_source' => Facture::TIERS_SOURCE_ORGANISATION,
                'tiers_type' => 'fournisseur',
                'tiers_id' => $devis->fournisseur_id,
                'date_emission' => now(),
                'reference_externe' => $devis->numero,
                'objet' => 'Facture — commande ' . $devis->commande?->numero_commande,
                'montant_ht' => $devis->montant_ht,
                'montant_tva' => $devis->montant_tva,
                'montant_ttc' => $devis->montant_ttc,
                'montant_regle' => 0,
                'statut' => 0,
                'created_by' => $request->user()->id,
            ]);
            $devis->update(['facture_id' => $facture->id]);
            // Lie aussi la facture à la commande fournisseur (colonne facture_id sur commande_fournisseurs)
            $devis->commande?->update(['facture_id' => $facture->id]);
        }

        return back()->with('success', 'Devis sélectionné' . (!empty($data['creer_facture']) ? ' + facture créée' : '') . '.');
    }

    public function rejeter(Request $request, DevisFournisseur $devis)
    {
        if (!$devis->peutEtreRejete()) return back()->with('error', 'Devis déjà décidé.');
        $data = $request->validate(['motivation' => 'required|string|min:3|max:2000']);
        $devis->rejeter($data['motivation'], $request->user()->id);
        return back()->with('success', 'Devis rejeté.');
    }

    /**
     * @param bool $piecesJointesObligatoires  true à la création (au moins 1 PJ requise), false à l'update.
     */
    private function validateData(Request $request, ?int $id = null, bool $piecesJointesObligatoires = false): array
    {
        $rules = [
            'commande_fournisseur_id' => 'required|exists:commande_fournisseurs,id',
            'fournisseur_id' => 'nullable|exists:intranet_contact_organisations,id',
            'date_reception' => 'required|date',
            'date_validite' => 'nullable|date|after_or_equal:date_reception',
            'delai_livraison_jours' => 'nullable|integer|min:0',
            'mode_reglement' => 'nullable|string|max:50',
            'montant_ht'  => 'required|numeric|min:0',
            'montant_ttc' => 'required|numeric|min:0|gte:montant_ht',
            'conditions' => 'nullable|string|max:5000',
            'commentaire' => 'nullable|string|max:5000',
            'pieces_jointes'   => ($piecesJointesObligatoires ? 'required|array|min:1' : 'nullable|array'),
            'pieces_jointes.*' => 'required|file|max:20480',
        ];
        return $request->validate($rules, [
            'pieces_jointes.required' => 'Au moins un devis PDF (ou photo scannée) est obligatoire.',
            'pieces_jointes.min'      => 'Au moins un devis PDF (ou photo scannée) est obligatoire.',
            'montant_ttc.gte'         => 'Le montant TTC doit être supérieur ou égal au HT.',
        ]);
    }
}
