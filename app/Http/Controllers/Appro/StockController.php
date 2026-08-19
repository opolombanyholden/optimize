<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\Emplacement;
use App\Models\Produit;
use App\Models\ProduitEmplacement;
use App\Models\ProduitMouvement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Tableau de bord stock : alertes, ruptures, dernières entrées/sorties.
     */
    public function dashboard(Request $request)
    {
        return view('appro.stock.dashboard', [
            'alertes'      => Produit::alertStock()->with('famille')->orderBy('stock_actuel')->limit(20)->get(),
            'ruptures'     => Produit::rupture()->orderBy('designation')->limit(20)->get(),
            'derniers'     => ProduitMouvement::with(['produit', 'emplacement'])->latest()->limit(20)->get(),
            'nbAlertes'    => Produit::alertStock()->count(),
            'nbRuptures'   => Produit::rupture()->count(),
            'valeurStock'  => Produit::where('est_stockable', true)
                                ->selectRaw('COALESCE(SUM(stock_actuel * prix_unitaire), 0) as total')
                                ->value('total'),
            'nbEmplacements' => Emplacement::where('actif', true)->count(),
        ]);
    }

    /**
     * Détail du stock par emplacement (ventilation + filtres).
     */
    public function parEmplacement(Request $request)
    {
        $stocks = ProduitEmplacement::query()
            ->with(['produit.famille', 'emplacement'])
            ->when($request->emplacement, function ($q, $e) {
                $emp = Emplacement::find($e);
                if ($emp) $q->whereIn('emplacement_id', $emp->descendantsIds());
            })
            ->when($request->q, fn($q, $s) => $q->whereHas('produit', fn($p) => $p->where('designation', 'ilike', "%{$s}%")->orWhere('code', 'ilike', "%{$s}%")))
            ->when($request->non_nul === 'oui', fn($q) => $q->where('quantite', '>', 0))
            ->orderBy('emplacement_id')
            ->paginate(30)
            ->withQueryString();

        return view('appro.stock.par-emplacement', [
            'stocks' => $stocks,
            'emplacements' => Emplacement::whereNull('parent_id')->with('enfants.enfants')->orderBy('ordre')->get(),
        ]);
    }

    /**
     * Journal complet des mouvements (filtrable).
     */
    public function mouvements(Request $request)
    {
        $mouvements = ProduitMouvement::query()
            ->with(['produit', 'emplacement', 'emplacementSource', 'user'])
            ->when($request->produit, fn($q, $p) => $q->where('produit_id', $p))
            ->when($request->emplacement, fn($q, $e) => $q->where(function ($sub) use ($e) {
                $sub->where('emplacement_id', $e)->orWhere('emplacement_source_id', $e);
            }))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->depuis, fn($q, $d) => $q->where('created_at', '>=', $d))
            ->when($request->jusqua, fn($q, $d) => $q->where('created_at', '<=', $d . ' 23:59:59'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('appro.stock.mouvements', [
            'mouvements' => $mouvements,
            'types' => ProduitMouvement::TYPES,
            'produits' => Produit::where('est_stockable', true)->orderBy('designation')->get(['id', 'code', 'designation']),
            'emplacements' => Emplacement::orderBy('libelle')->get(['id', 'libelle', 'code']),
        ]);
    }
}
