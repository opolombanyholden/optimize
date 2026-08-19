<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Services\Finance\OperationFinanciereService;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $factures = Facture::query()
            ->when($request->sens, fn($q, $s) => $q->where('sens', $s))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->when($request->q, fn($q, $s) => $q->where(fn($w) => $w->where('numero', 'ilike', "%$s%")->orWhere('objet', 'ilike', "%$s%")))
            ->when($request->date_debut, fn($q, $d) => $q->where('date_emission', '>=', $d))
            ->when($request->date_fin, fn($q, $d) => $q->where('date_emission', '<=', $d))
            ->orderByDesc('date_emission')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('finance.factures.index', compact('factures'));
    }

    public function create(Request $request)
    {
        $sens = $request->sens ?? 'depense';
        // Depuis la fusion Client/Fournisseur → ContactOrganisation typé (2026-07-09),
        // les tiers sont des ContactOrganisation filtrés par `type` — même liste que
        // /intranet/organisations?type=fournisseur (pas de filtre supplémentaire par statut).
        $fournisseurs = \App\Models\Intranet\ContactOrganisation::where('type', 'fournisseur')
            ->orderBy('nom')->get();
        $clients      = \App\Models\Intranet\ContactOrganisation::where('type', 'client')
            ->orderBy('nom')->get();
        $exercices = Exercice::orderByDesc('id')->get();
        $facture = new Facture(['sens' => $sens, 'date_emission' => now()->toDateString(), 'taux_tva' => 0]);
        return view('finance.factures.create', compact('sens', 'fournisseurs', 'clients', 'exercices', 'facture'));
    }

    public function store(Request $request)
    {
        $data = $this->validateFacture($request);
        $data = $this->normaliserTiersFacture($data);

        $data['numero']      = OperationFinanciereService::genererNumeroFacture($data['sens']);
        $data = $this->calculerMontantsTva($data);
        $data['statut']      = 0; // brouillon
        $data['created_by']  = auth()->id();

        $facture = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request) {
            $facture = Facture::create($data);
            if ($request->hasFile('pieces_jointes')) {
                $facture->attacherFichiers($request->file('pieces_jointes'), 'finance/factures/' . $facture->id);
            }
            return $facture;
        });
        return redirect()->route('finance.factures.show', $facture)
            ->with('success', "Facture {$facture->numero} créée en brouillon.");
    }

    /**
     * Suppression d'une pièce jointe attachée à une facture.
     */
    public function destroyPieceJointe(Facture $facture, \App\Models\Intranet\PieceJointe $piece)
    {
        abort_unless(
            $piece->attachable_id === $facture->id && $piece->attachable_type === Facture::class,
            404
        );
        if (!$facture->est_modifiable && !auth()->user()->hasRole('super-admin')) {
            return back()->with('error', "Facture non modifiable — impossible de supprimer les pièces jointes.");
        }
        $facture->detacherFichier($piece->id);
        return back()->with('success', 'Pièce jointe supprimée.');
    }

    // ─── Helpers ─────────────────────────────

    private function validateFacture(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'sens'             => 'required|in:depense,recette',
            'tiers_type'       => 'required|in:fournisseur,client',
            'tiers_source'     => 'nullable|in:organisation,externe',
            'tiers_id'         => 'nullable|integer',
            'tiers_infos.nom'         => 'nullable|string|max:255',
            'tiers_infos.raison_sociale' => 'nullable|string|max:255',
            'tiers_infos.adresse'     => 'nullable|string|max:500',
            'tiers_infos.telephone'   => 'nullable|string|max:50',
            'tiers_infos.email'       => 'nullable|email|max:255',
            'tiers_infos.nif'         => 'nullable|string|max:50',
            'exercice_id'      => 'nullable|exists:exercices,id',
            'date_emission'    => 'required|date',
            'date_echeance'    => 'nullable|date|after_or_equal:date_emission',
            'reference_externe'=> 'nullable|string|max:100',
            'objet'            => 'required|string|max:500',
            'montant_ht'       => 'required|numeric|min:0',
            'montant_tva'      => 'nullable|numeric|min:0',
            'taux_tva'         => 'nullable|numeric|min:0|max:100', // conservé pour compat descendante
            'commentaire'      => 'nullable|string',
            'pieces_jointes'   => 'nullable|array',
            'pieces_jointes.*' => 'file|max:20480|mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip',
        ]);
    }

    /**
     * Calcule les montants TVA et TTC selon les données reçues.
     * - `montant_tva` (saisie directe du Total Taxe) prend priorité sur `taux_tva`.
     * - `taux_tva` est rétro-calculé à titre indicatif (montant_tva / HT × 100).
     * - TTC = HT + Total Taxe.
     */
    private function calculerMontantsTva(array $data): array
    {
        $ht = (float) ($data['montant_ht'] ?? 0);

        if (array_key_exists('montant_tva', $data) && $data['montant_tva'] !== null && $data['montant_tva'] !== '') {
            // Saisie directe du Total Taxe
            $tva = round((float) $data['montant_tva'], 2);
            $data['montant_tva'] = $tva;
            $data['taux_tva']    = $ht > 0 ? round(($tva / $ht) * 100, 2) : 0;
        } else {
            // Rétrocompatibilité : si un taux est envoyé (ancienne UX), on calcule le montant
            $taux = (float) ($data['taux_tva'] ?? 0);
            $data['montant_tva'] = round(($ht * $taux) / 100, 2);
        }
        $data['montant_ttc'] = round($ht + $data['montant_tva'], 2);
        return $data;
    }

    /**
     * Normalise le tiers de la facture :
     *  - source `organisation` → valide l'existence et le type sur ContactOrganisation
     *  - source `externe` → nettoie les infos et impose nom obligatoire, tiers_id à null
     */
    private function normaliserTiersFacture(array $data): array
    {
        $source = $data['tiers_source'] ?? Facture::TIERS_SOURCE_ORGANISATION;
        $data['tiers_source'] = $source;

        if ($source === Facture::TIERS_SOURCE_EXTERNE) {
            $infos = array_filter($data['tiers_infos'] ?? [], fn($v) => $v !== null && $v !== '');
            if (empty($infos['nom']) && empty($infos['raison_sociale'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tiers_infos.nom' => 'Le nom ou la raison sociale est obligatoire pour un tiers externe.',
                ]);
            }
            $data['tiers_infos_json'] = $infos;
            $data['tiers_id']         = null;
        } else {
            $tiers = \App\Models\Intranet\ContactOrganisation::where('id', $data['tiers_id'] ?? 0)
                ->where('type', $data['tiers_type'])->first();
            if (!$tiers) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tiers_id' => 'Tiers introuvable ou type incorrect.',
                ]);
            }
            $data['tiers_infos_json'] = null;
        }
        unset($data['tiers_infos']);
        return $data;
    }

    public function show(Facture $facture)
    {
        $facture->load([
            'exercice', 'createur', 'validateur', 'annuleur', 'operations',
            'piecesJointes',
            'ordres.compte', 'ordres.createur',
        ]);
        $tiers = $facture->tiersResolu();
        return view('finance.factures.show', compact('facture', 'tiers'));
    }

    public function edit(Facture $facture)
    {
        if (!$facture->est_modifiable) {
            return back()->with('error', 'Seule une facture en brouillon peut être éditée.');
        }
        // Si la facture est issue d'un devis (donc d'une consultation commande fournisseur),
        // limiter le choix des fournisseurs à ceux ayant fourni un devis pour cette commande.
        $devisSource = $facture->devisSource()->with('commande')->first();
        $commande = $devisSource?->commande;

        if ($commande) {
            $fournisseurIds = \App\Models\DevisFournisseur::where('commande_fournisseur_id', $commande->id)
                ->whereNotNull('fournisseur_id')->pluck('fournisseur_id')->unique()->all();
            $fournisseurs = \App\Models\Intranet\ContactOrganisation::whereIn('id', $fournisseurIds)
                ->orderBy('nom')->get();
        } else {
            $fournisseurs = \App\Models\Intranet\ContactOrganisation::where('type', 'fournisseur')
                ->orderBy('nom')->get();
        }
        $clients = \App\Models\Intranet\ContactOrganisation::where('type', 'client')
            ->orderBy('nom')->get();
        $exercices = Exercice::orderByDesc('id')->get();
        return view('finance.factures.edit', compact('facture', 'fournisseurs', 'clients', 'exercices', 'commande'));
    }

    public function update(Request $request, Facture $facture)
    {
        if (!$facture->est_modifiable) {
            return back()->with('error', 'Facture non modifiable.');
        }
        $data = $this->validateFacture($request, $facture->id);
        // sens n'est pas modifiable après création → on le préserve
        $data['sens'] = $facture->sens;
        $data = $this->normaliserTiersFacture($data);
        $data = $this->calculerMontantsTva($data);

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $facture, $request) {
            $facture->update($data);
            if ($request->hasFile('pieces_jointes')) {
                $facture->attacherFichiers($request->file('pieces_jointes'), 'finance/factures/' . $facture->id);
            }
        });
        return redirect()->route('finance.factures.show', $facture)->with('success', 'Facture mise à jour.');
    }

    public function valider(Facture $facture)
    {
        if (!$facture->est_validable) {
            return back()->with('error', 'Seule une facture en brouillon peut être validée.');
        }
        $facture->update([
            'statut'     => 1,
            'valide_par' => auth()->id(),
            'valide_at'  => now(),
        ]);
        return back()->with('success', "Facture {$facture->numero} validée.");
    }

    public function annuler(Request $request, Facture $facture)
    {
        if (!$facture->est_annulable) {
            return back()->with('error', 'Cette facture ne peut pas être annulée.');
        }
        $data = $request->validate(['motif_annulation' => 'required|string|max:500']);
        $facture->update([
            'statut'           => 4,
            'motif_annulation' => $data['motif_annulation'],
            'annule_par'       => auth()->id(),
            'annule_at'        => now(),
        ]);
        return back()->with('success', 'Facture annulée.');
    }

    public function destroy(Facture $facture)
    {
        if ($facture->statut !== 0) {
            return back()->with('error', 'Seule une facture en brouillon peut être supprimée.');
        }
        $facture->delete();
        return redirect()->route('finance.factures.index')->with('success', 'Facture supprimée.');
    }

    /**
     * Ordonnance la facture (OK ordonnateur). Prérequis à tout paiement.
     */
    public function ordonnancer(Request $request, Facture $facture)
    {
        if (!$facture->peutEtreOrdonnancee()) {
            return back()->with('error', 'Cette facture ne peut pas être ordonnancée (déjà ordonnancée ou sens recette).');
        }
        $data = $request->validate(['motivation' => 'nullable|string|max:2000']);
        $facture->ordonnancer($data['motivation'] ?? null, $request->user()->id);
        return back()->with('success', 'Facture ordonnancée — OK pour paiement.');
    }
}
