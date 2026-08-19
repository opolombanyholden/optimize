<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Approvisionnement;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApproController extends Controller
{
    public function fournisseurs(Request $request): JsonResponse
    {
        $query = Fournisseur::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('raison_sociale', 'like', "%{$request->search}%")
                  ->orWhere('nif', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $fournisseurs = $query->orderBy('raison_sociale')->paginate($request->input('per_page', 15));

        return response()->json($fournisseurs);
    }

    public function showFournisseur(Fournisseur $fournisseur): JsonResponse
    {
        $fournisseur->load('commandes', 'produits');

        return response()->json($fournisseur);
    }

    public function produits(Request $request): JsonResponse
    {
        $query = Produit::query();

        if ($request->filled('search')) {
            $query->where('designation', 'like', "%{$request->search}%");
        }

        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }

        $produits = $query->orderBy('designation')->paginate($request->input('per_page', 15));

        return response()->json($produits);
    }

    public function commandes(Request $request): JsonResponse
    {
        $query = CommandeFournisseur::with('fournisseur');

        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $commandes = $query->orderByDesc('date_commande')->paginate($request->input('per_page', 15));

        return response()->json($commandes);
    }

    public function showCommande(CommandeFournisseur $commande): JsonResponse
    {
        $commande->load('fournisseur', 'produits');

        return response()->json($commande);
    }

    public function approvisionnements(Request $request): JsonResponse
    {
        $query = Approvisionnement::with('fournisseur');

        if ($request->filled('fournisseur_id')) {
            $query->where('fournisseur_id', $request->fournisseur_id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $appros = $query->orderByDesc('date_approvisionnement')->paginate($request->input('per_page', 15));

        return response()->json($appros);
    }
}
