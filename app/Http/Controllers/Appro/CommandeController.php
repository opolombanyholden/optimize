<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CommandeFournisseur;
use App\Models\CommandeLigne;
use App\Models\Facture;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function index(Request $request)
    {
        $commandes = CommandeFournisseur::query()
            ->with(['fournisseur', 'lignes'])
            ->when($request->q, fn($q, $s) => $q->where('numero_commande', 'ilike', "%{$s}%"))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->fournisseur, fn($q, $f) => $q->where('fournisseur_id', $f))
            ->orderByDesc('date_commande')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('appro.commandes.index', [
            'commandes'    => $commandes,
            'statuts'      => CommandeFournisseur::STATUTS,
            'fournisseurs' => ContactOrganisation::where('type', 'fournisseur')->actif()->orderBy('nom')->get(),
        ]);
    }

    public function create()
    {
        return view('appro.commandes.create', [
            'commande'     => new CommandeFournisseur(['date_commande' => now()->toDateString(), 'statut' => CommandeFournisseur::STATUT_BROUILLON]),
            'fournisseurs' => ContactOrganisation::where('type', 'fournisseur')->actif()->orderBy('nom')->get(),
            'produits'     => Produit::orderBy('designation')->get(['id', 'code', 'designation', 'prix_unitaire', 'stock_actuel', 'unite_mesure']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['numero_commande'] = $data['numero_commande'] ?? $this->genererNumero();
        $data['statut']          = CommandeFournisseur::STATUT_BROUILLON;
        $data['created_by']      = auth()->id();

        $commande = DB::transaction(function () use ($data, $request) {
            $c = CommandeFournisseur::create($data);
            $this->syncLignes($c, $request->input('lignes', []));
            $c->recalculerMontants();
            return $c;
        });
        return redirect()->route('appro.commandes.show', $commande)
            ->with('success', "Commande {$commande->numero_commande} créée en brouillon.");
    }

    public function show(CommandeFournisseur $commande)
    {
        $commande->load(['fournisseur', 'lignes.produit', 'facture', 'createur', 'approbateur', 'livreur', 'mouvements.produit']);
        return view('appro.commandes.show', compact('commande'));
    }

    public function edit(CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreModifiee() && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', "Cette commande n'est plus modifiable ({$commande->statut_libelle}).");
        }
        return view('appro.commandes.edit', [
            'commande'     => $commande->load('lignes'),
            'fournisseurs' => ContactOrganisation::where('type', 'fournisseur')->actif()->orderBy('nom')->get(),
            'produits'     => Produit::orderBy('designation')->get(['id', 'code', 'designation', 'prix_unitaire', 'stock_actuel', 'unite_mesure']),
        ]);
    }

    public function update(Request $request, CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreModifiee() && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', "Modification interdite ({$commande->statut_libelle}).");
        }
        $data = $this->validated($request);
        DB::transaction(function () use ($commande, $data, $request) {
            $commande->update($data);
            $this->syncLignes($commande, $request->input('lignes', []));
            $commande->recalculerMontants();
        });
        return redirect()->route('appro.commandes.show', $commande)->with('success', 'Commande mise à jour.');
    }

    public function destroy(CommandeFournisseur $commande)
    {
        $isSuperAdmin = auth()->user()->hasRole('super-admin');
        if ($commande->statut !== CommandeFournisseur::STATUT_BROUILLON && !$isSuperAdmin) {
            return back()->with('error', "Seule une commande en brouillon peut être supprimée. Utilisez « Annuler ».");
        }
        $commande->delete();
        return redirect()->route('appro.commandes.index')->with('success', 'Commande supprimée.');
    }

    // ═════════ WORKFLOW ═════════

    public function soumettre(CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreSoumise()) {
            return back()->with('error', "Impossible de soumettre (statut ou aucune ligne).");
        }
        $commande->soumettre();
        return back()->with('success', "Commande {$commande->numero_commande} soumise pour approbation.");
    }

    public function approuver(CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreApprouvee()) {
            return back()->with('error', "Approbation impossible ({$commande->statut_libelle}).");
        }
        $commande->approuver();
        return back()->with('success', "Commande {$commande->numero_commande} approuvée.");
    }

    public function livrer(Request $request, CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreLivree()) {
            return back()->with('error', "Livraison impossible ({$commande->statut_libelle}).");
        }
        $data = $request->validate([
            'quantites'      => 'nullable|array',
            'quantites.*'    => 'nullable|numeric|min:0',
            'emplacements'   => 'nullable|array',
            'emplacements.*' => 'nullable|exists:emplacements,id',
        ]);
        $commande->livrer(
            $data['quantites'] ?? [],
            null,
            array_filter($data['emplacements'] ?? []),
        );
        return back()->with('success', "Commande {$commande->numero_commande} livrée. Stocks mis à jour.");
    }

    public function annuler(Request $request, CommandeFournisseur $commande)
    {
        if (!$commande->peutEtreAnnulee()) {
            return back()->with('error', "Annulation impossible ({$commande->statut_libelle}).");
        }
        $data = $request->validate(['motif_annulation' => 'required|string|min:10|max:500']);
        $commande->annuler($data['motif_annulation']);
        return back()->with('success', "Commande {$commande->numero_commande} annulée.");
    }

    /**
     * Génère une facture dépense pré-remplie depuis une commande livrée.
     */
    public function genererFacture(CommandeFournisseur $commande)
    {
        if (!$commande->peutGenererFacture()) {
            return back()->with('error', "Génération impossible : commande non livrée ou déjà facturée.");
        }
        $facture = DB::transaction(function () use ($commande) {
            $facture = Facture::create([
                'numero'        => 'FA-' . str_pad((string) $commande->id, 6, '0', STR_PAD_LEFT),
                'sens'          => 'depense',
                'tiers_type'    => 'fournisseur',
                'tiers_source'  => Facture::TIERS_SOURCE_ORGANISATION,
                'tiers_id'      => $commande->fournisseur_id,
                'date_emission' => now()->toDateString(),
                'objet'         => 'Facture commande ' . $commande->numero_commande,
                'montant_ht'    => $commande->montant_ht,
                'taux_tva'      => 0,
                'montant_tva'   => $commande->montant_tva,
                'montant_ttc'   => $commande->montant_ttc,
                'commentaire'   => "Générée depuis la commande {$commande->numero_commande}",
                'statut'        => 0,
                'created_by'    => auth()->id(),
            ]);
            $commande->update(['facture_id' => $facture->id]);
            return $facture;
        });
        return redirect()->route('finance.factures.show', $facture)
            ->with('success', "Facture {$facture->numero} générée depuis la commande {$commande->numero_commande}.");
    }

    // ─── Helpers ────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'numero_commande'         => 'nullable|string|max:100',
            'objet'                   => 'nullable|string|max:255',
            'fournisseur_id'          => 'nullable|integer|exists:intranet_contact_organisations,id',
            'date_commande'           => 'required|date',
            'date_livraison_prevue'   => 'nullable|date|after_or_equal:date_commande',
            'mode_reglement'          => 'nullable|string|in:' . implode(',', array_keys(\App\Models\CommandeFournisseur::MODES_REGLEMENT)),
            'conditions'              => 'nullable|string|max:2000',
            'commentaire'             => 'nullable|string|max:2000',
            'lignes'                  => 'nullable|array',
            'lignes.*.id'             => 'nullable|integer',
            'lignes.*.produit_id'     => 'nullable|integer|exists:produits,id',
            'lignes.*.designation'    => 'nullable|string|max:255',
            'lignes.*.quantite_commandee' => 'nullable|numeric|min:0',
            'lignes.*.prix_unitaire'  => 'nullable|numeric|min:0',
            'lignes.*.observation'    => 'nullable|string|max:500',
            'lignes.*._delete'        => 'nullable|boolean',
        ]);
    }

    private function syncLignes(CommandeFournisseur $commande, array $items): void
    {
        foreach ($items as $idx => $item) {
            if (!empty($item['_delete']) && !empty($item['id'])) {
                CommandeLigne::where('id', $item['id'])->where('commande_id', $commande->id)->delete();
                continue;
            }
            $qte = (float) ($item['quantite_commandee'] ?? 0);
            $pu  = (float) ($item['prix_unitaire'] ?? 0);
            $lbl = trim((string) ($item['designation'] ?? ''));
            $produitId = $item['produit_id'] ?? null;

            if ($produitId && $lbl === '') {
                $prod = Produit::find($produitId);
                $lbl  = $prod?->designation ?? 'Article';
                if ($pu <= 0 && $prod?->prix_unitaire > 0) $pu = (float) $prod->prix_unitaire;
            }
            if ($lbl === '' && $qte <= 0 && $pu <= 0) continue;

            $payload = [
                'commande_id'        => $commande->id,
                'produit_id'         => $produitId ?: null,
                'designation'        => $lbl ?: 'Article',
                'quantite_commandee' => $qte ?: 1,
                'prix_unitaire'      => $pu,
                'observation'        => $item['observation'] ?? null,
                'ordre'              => ($idx + 1) * 10,
            ];
            if (!empty($item['id'])) {
                $l = CommandeLigne::where('id', $item['id'])->where('commande_id', $commande->id)->first();
                $l?->update($payload);
            } else {
                CommandeLigne::create($payload);
            }
        }
    }

    private function genererNumero(): string
    {
        $seq = CommandeFournisseur::whereYear('created_at', now()->year)->count() + 1;
        return sprintf('CMD-%d-%04d', now()->year, $seq);
    }
}
