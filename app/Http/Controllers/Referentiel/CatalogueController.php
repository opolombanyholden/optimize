<?php

namespace App\Http\Controllers\Referentiel;

use App\Http\Controllers\Controller;
use App\Models\Emplacement;
use App\Models\FamilleArticle;
use App\Models\InventaireLigne;
use App\Models\Produit;
use App\Models\ProduitMouvement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $articles = Produit::query()
            ->with('famille')
            ->when($request->q, fn($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('designation', 'ilike', "%{$s}%")->orWhere('code', 'ilike', "%{$s}%");
            }))
            ->when($request->famille, function ($q, $f) {
                $famille = FamilleArticle::find($f);
                if ($famille) $q->whereIn('famille_id', $famille->descendantsIds());
            })
            ->when($request->type, fn($q, $t) => $q->where('type_article', $t))
            ->when($request->alerte === 'oui', fn($q) => $q->alertStock())
            ->orderBy('designation')
            ->paginate(20)
            ->withQueryString();

        return view('referentiel.catalogue.index', [
            'articles' => $articles,
            'familles' => FamilleArticle::whereNull('parent_id')->with('enfants.enfants')->orderBy('ordre')->get(),
            'types' => Produit::TYPES,
            'alertes' => Produit::alertStock()->count(),
            'ruptures' => Produit::rupture()->count(),
        ]);
    }

    public function create()
    {
        return view('referentiel.catalogue.create', [
            'article' => new Produit(['type_article' => 'bien', 'est_stockable' => true, 'statut' => 1]),
            'familles' => FamilleArticle::orderBy('libelle')->get(),
            'types' => Produit::TYPES,
            'emplacements' => \App\Models\Emplacement::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Produit::create($this->validateData($request));
        return redirect()->route('referentiel.catalogue.index')->with('success', 'Article ajouté au catalogue.');
    }

    public function show(Produit $article)
    {
        $article->load(['famille', 'emplacementPrincipal', 'stocksParEmplacement.emplacement']);
        return view('referentiel.catalogue.show', [
            'article' => $article,
            'types' => Produit::TYPES,
            'mouvements' => ProduitMouvement::with(['emplacement', 'emplacementSource', 'user'])
                ->where('produit_id', $article->id)->latest()->limit(15)->get(),
            'inventairesLignes' => InventaireLigne::with(['inventaire', 'emplacement'])
                ->where('produit_id', $article->id)
                ->orderByDesc('created_at')->limit(10)->get(),
            'valeurStock' => (float) $article->stock_actuel * (float) $article->prix_unitaire,
        ]);
    }

    public function edit(Produit $article)
    {
        return view('referentiel.catalogue.edit', [
            'article' => $article,
            'familles' => FamilleArticle::orderBy('libelle')->get(),
            'types' => Produit::TYPES,
            'emplacements' => Emplacement::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function formAjusterStock(Produit $article)
    {
        if (!$article->est_stockable) abort(404);
        $article->load('stocksParEmplacement.emplacement');
        return view('referentiel.catalogue.ajuster-stock', [
            'article' => $article,
            'emplacements' => Emplacement::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function formTransfererStock(Produit $article)
    {
        if (!$article->est_stockable) abort(404);
        $article->load('stocksParEmplacement.emplacement');
        if ($article->stocksParEmplacement->where('quantite', '>', 0)->isEmpty()) {
            return redirect()->route('referentiel.catalogue.show', $article)
                ->with('error', 'Aucun emplacement source disponible — commencez par un ajustement.');
        }
        return view('referentiel.catalogue.transferer-stock', [
            'article' => $article,
            'emplacements' => Emplacement::where('actif', true)->orderBy('libelle')->get(),
        ]);
    }

    public function update(Request $request, Produit $article)
    {
        $article->update($this->validateData($request, $article->id));
        return redirect()->route('referentiel.catalogue.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(Produit $article)
    {
        $article->delete();
        return back()->with('success', 'Article supprimé.');
    }

    /**
     * Ajustement manuel de stock sur un emplacement (positif ou négatif).
     * Crée un ProduitMouvement type=ajustement et met à jour pivot + agrégat.
     */
    public function ajusterStock(Request $request, Produit $article)
    {
        if (!$article->est_stockable) return back()->with('error', 'Article non stockable.');
        $data = $request->validate([
            'emplacement_id' => 'required|exists:emplacements,id',
            'delta' => 'required|numeric|not_in:0',
            'motif' => 'required|string|min:3|max:255',
        ]);
        $delta = (float) $data['delta'];
        // Empêcher un ajustement négatif qui rendrait le stock local < 0
        $stockLocal = $article->stockDans($data['emplacement_id']);
        if ($delta < 0 && ($stockLocal + $delta) < 0) {
            return back()->with('error', "Ajustement impossible : stock local {$stockLocal}, delta {$delta} rendrait le stock négatif.");
        }
        DB::transaction(function () use ($article, $data, $delta, $request) {
            $article->ajusterStockEmplacement((int) $data['emplacement_id'], $delta);
            ProduitMouvement::create([
                'produit_id'     => $article->id,
                'type'           => ProduitMouvement::TYPE_AJUSTEMENT,
                'quantite'       => abs($delta),
                'stock_apres'    => (float) $article->fresh()->stock_actuel,
                'emplacement_id' => $data['emplacement_id'],
                'reference'      => 'AJUST-' . now()->format('YmdHis'),
                'motif'          => $data['motif'] . ($delta > 0 ? ' (positif)' : ' (négatif)'),
                'source_type'    => 'manuel',
                'source_id'      => 0,
                'user_id'        => $request->user()->id,
            ]);
        });
        return redirect()->route('referentiel.catalogue.show', $article)
            ->with('success', "Ajustement de {$delta} enregistré.");
    }

    /**
     * Transfert de quantité entre 2 emplacements.
     * Crée un mouvement type=transfert et déplace le stock.
     */
    public function transfererStock(Request $request, Produit $article)
    {
        if (!$article->est_stockable) return back()->with('error', 'Article non stockable.');
        $data = $request->validate([
            'emplacement_source_id' => 'required|exists:emplacements,id',
            'emplacement_dest_id' => 'required|exists:emplacements,id|different:emplacement_source_id',
            'quantite' => 'required|numeric|gt:0',
            'motif' => 'nullable|string|max:255',
        ]);
        $qte = (float) $data['quantite'];
        $stockSource = $article->stockDans($data['emplacement_source_id']);
        if ($qte > $stockSource) {
            return back()->with('error', "Transfert impossible : stock source disponible {$stockSource}, demandé {$qte}.");
        }
        DB::transaction(function () use ($article, $data, $qte, $request) {
            $article->ajusterStockEmplacement((int) $data['emplacement_source_id'], -$qte);
            $article->ajusterStockEmplacement((int) $data['emplacement_dest_id'], $qte);
            ProduitMouvement::create([
                'produit_id'            => $article->id,
                'type'                  => ProduitMouvement::TYPE_TRANSFERT,
                'quantite'              => $qte,
                'stock_apres'           => (float) $article->fresh()->stock_actuel,
                'emplacement_source_id' => $data['emplacement_source_id'],
                'emplacement_id'        => $data['emplacement_dest_id'],
                'reference'             => 'TRSF-' . now()->format('YmdHis'),
                'motif'                 => $data['motif'] ?? 'Transfert entre emplacements',
                'source_type'           => 'manuel',
                'source_id'             => 0,
                'user_id'               => $request->user()->id,
            ]);
        });
        return redirect()->route('referentiel.catalogue.show', $article)
            ->with('success', "Transfert de {$qte} effectué.");
    }

    private function validateData(Request $request, ?int $id = null): array
    {
        $unique = 'unique:produits,code' . ($id ? ",{$id}" : '');
        return $request->validate([
            'code' => "nullable|string|max:50|{$unique}",
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'famille_id' => 'nullable|exists:familles_articles,id',
            'type_article' => 'required|in:bien,service',
            'est_stockable' => 'nullable|boolean',
            'unite_mesure' => 'nullable|string|max:30',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'taux_tva' => 'nullable|numeric|min:0|max:100',
            // stock_actuel non validé ici : c'est un agrégat calculé depuis produit_emplacements.
            // Il n'est modifiable QUE via ajusterStock / transfererStock / livraisons / inventaires.
            'stock_minimum' => 'nullable|numeric|min:0',
            'seuil_alerte' => 'nullable|numeric|min:0',
            'stock_maximum' => 'nullable|numeric|min:0',
            'emplacement' => 'nullable|string|max:255',
            'emplacement_id' => 'nullable|exists:emplacements,id',
            'statut' => 'nullable|integer|in:0,1',
        ]);
    }
}
