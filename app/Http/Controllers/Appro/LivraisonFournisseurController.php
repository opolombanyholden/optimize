<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CommandeFournisseur;
use App\Models\CommandeLigne;
use App\Models\Emplacement;
use App\Models\LivraisonFournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LivraisonFournisseurController extends Controller
{
    public function index(Request $request)
    {
        $livraisons = LivraisonFournisseur::query()
            ->with(['commande.fournisseur', 'emplacementReception', 'receptionneur'])
            ->when($request->q, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('numero', 'ilike', "%{$s}%")->orWhere('bon_livraison_ref', 'ilike', "%{$s}%");
            }))
            ->when($request->commande, fn($q, $c) => $q->where('commande_fournisseur_id', $c))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->depuis, fn($q, $d) => $q->whereDate('date_livraison', '>=', $d))
            ->when($request->jusqua, fn($q, $d) => $q->whereDate('date_livraison', '<=', $d))
            ->orderByDesc('date_livraison')
            ->paginate(20)
            ->withQueryString();

        return view('appro.livraisons-fournisseur.index', [
            'livraisons' => $livraisons,
            'types' => LivraisonFournisseur::TYPES,
        ]);
    }

    /**
     * Formulaire de création — soit sans contexte (choix libre de la commande), soit rattachée à une commande.
     */
    public function create(Request $request)
    {
        $commande = null;
        if ($request->filled('commande_id')) {
            $commande = CommandeFournisseur::with(['lignes.produit', 'fournisseur'])->find($request->commande_id);
            if (!$commande || !$commande->peutRecevoirLivraison()) {
                return redirect()->route('appro.commandes.index')
                    ->with('error', $commande ? "Commande {$commande->numero_commande} ne peut plus recevoir de livraison ({$commande->statut_libelle})." : 'Commande introuvable.');
            }
        }

        return view('appro.livraisons-fournisseur.create', [
            'commande' => $commande,
            'commandes' => $commande ? collect() : CommandeFournisseur::with('fournisseur')
                ->whereIn('statut', [CommandeFournisseur::STATUT_APPROUVEE, CommandeFournisseur::STATUT_LIVREE_PARTIELLE])
                ->orderByDesc('date_commande')->get(),
            'emplacements' => Emplacement::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'commande_fournisseur_id' => 'required|exists:commande_fournisseurs,id',
            'date_livraison'          => 'required|date',
            'emplacement_reception_id' => 'nullable|exists:emplacements,id',
            'bon_livraison_ref'       => 'nullable|string|max:100',
            'commentaire'             => 'nullable|string|max:2000',
            'lignes'                  => 'required|array|min:1',
            'lignes.*.commande_ligne_id' => 'required|exists:commande_lignes,id',
            'lignes.*.quantite'       => 'required|numeric|min:0',
            'lignes.*.emplacement_id' => 'nullable|exists:emplacements,id',
            'lignes.*.observation'    => 'nullable|string|max:500',
            'pieces_jointes.*'        => 'nullable|file|max:20480',
        ]);

        $commande = CommandeFournisseur::with('lignes')->findOrFail($data['commande_fournisseur_id']);
        if (!$commande->peutRecevoirLivraison()) {
            return back()->with('error', "Commande {$commande->numero_commande} n'accepte plus de livraisons ({$commande->statut_libelle}).");
        }

        // Validation métier : quantités ne dépassent pas le reste à livrer
        $ligneMap = $commande->lignes->keyBy('id');
        foreach ($data['lignes'] as $lg) {
            $cl = $ligneMap->get($lg['commande_ligne_id']);
            if (!$cl) return back()->with('error', "Ligne commande #{$lg['commande_ligne_id']} introuvable.");
            $reste = (float) $cl->quantite_commandee - (float) $cl->quantite_livree;
            if ((float) $lg['quantite'] > $reste + 0.0005) {
                return back()->with('error', "Quantité {$lg['quantite']} dépasse le reste à livrer {$reste} pour « {$cl->designation} ».");
            }
        }

        // Ignorer les lignes à quantité 0
        $lignesUtiles = array_filter($data['lignes'], fn($lg) => (float) $lg['quantite'] > 0);
        if (empty($lignesUtiles)) return back()->with('error', 'Aucune quantité à livrer.');

        $livraison = DB::transaction(function () use ($commande, $data, $lignesUtiles, $request, $ligneMap) {
            $liv = LivraisonFournisseur::create([
                'numero'                   => LivraisonFournisseur::genererNumero(),
                'commande_fournisseur_id'  => $commande->id,
                'date_livraison'           => $data['date_livraison'],
                'emplacement_reception_id' => $data['emplacement_reception_id'] ?? null,
                'bon_livraison_ref'        => $data['bon_livraison_ref'] ?? null,
                'commentaire'              => $data['commentaire'] ?? null,
                'type'                     => LivraisonFournisseur::TYPE_PARTIELLE, // recalculé par appliquer()
            ]);
            foreach ($lignesUtiles as $lg) {
                $cl = $ligneMap->get($lg['commande_ligne_id']);
                $liv->lignes()->create([
                    'commande_ligne_id' => $lg['commande_ligne_id'],
                    'produit_id'        => $cl->produit_id,
                    'emplacement_id'    => $lg['emplacement_id'] ?? null,
                    'quantite'          => (float) $lg['quantite'],
                    'observation'       => $lg['observation'] ?? null,
                ]);
            }
            if ($request->hasFile('pieces_jointes')) {
                $liv->attacherFichiers($request->file('pieces_jointes'), 'appro/livraisons-fournisseur');
            }
            $liv->appliquer($request->user()->id);
            return $liv;
        });

        return redirect()->route('appro.livraisons-fournisseur.show', $livraison)
            ->with('success', "Livraison {$livraison->numero} enregistrée (type : {$livraison->type}).");
    }

    public function show(LivraisonFournisseur $livraison)
    {
        $livraison->load([
            'commande.fournisseur', 'commande.lignes',
            'lignes.commandeLigne', 'lignes.produit', 'lignes.emplacement',
            'emplacementReception', 'receptionneur', 'piecesJointes',
        ]);
        return view('appro.livraisons-fournisseur.show', compact('livraison'));
    }
}
