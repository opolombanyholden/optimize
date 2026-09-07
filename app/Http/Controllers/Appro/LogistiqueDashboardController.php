<?php

namespace App\Http\Controllers\Appro;

use App\Http\Controllers\Controller;
use App\Models\CommandeFournisseur;
use App\Models\CommandeInterne;
use App\Models\Dysfonctionnement;
use App\Models\Immobilisation;
use App\Models\Intervention;
use App\Models\Facture;
use App\Models\ContratFournisseur;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Produit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LogistiqueDashboardController extends Controller
{
    public function index()
    {
        $now       = now();
        $startMois = $now->copy()->startOfMonth();
        $moisPrec  = $now->copy()->subMonth();

        $enCoursStatuts = [
            CommandeFournisseur::STATUT_SOUMISE,
            CommandeFournisseur::STATUT_APPROUVEE,
            CommandeFournisseur::STATUT_LIVREE_PARTIELLE,
        ];

        // ── ACHATS — vue synthétique ────────────────────────────
        $commandesEnCours  = CommandeFournisseur::whereIn('statut', $enCoursStatuts)->count();
        $commandesTotal    = CommandeFournisseur::count();
        $derniersCommandes = CommandeFournisseur::with(['fournisseur'])
            ->latest('date_commande')->take(5)->get();

        // ── KPI FINANCIER : montant engagé ce mois vs mois précédent
        $montantMois     = (float) CommandeFournisseur::where('statut', '!=', CommandeFournisseur::STATUT_ANNULEE)
            ->whereBetween('date_commande', [$startMois, $now])
            ->sum('montant_ttc');
        $montantMoisPrec = (float) CommandeFournisseur::where('statut', '!=', CommandeFournisseur::STATUT_ANNULEE)
            ->whereBetween('date_commande', [
                $moisPrec->copy()->startOfMonth(),
                $moisPrec->copy()->endOfMonth(),
            ])
            ->sum('montant_ttc');
        $tendanceMontant = $montantMoisPrec > 0
            ? round((($montantMois - $montantMoisPrec) / $montantMoisPrec) * 100)
            : ($montantMois > 0 ? 100 : 0);

        // ── KPI FINANCIER : nb commandes ce mois vs précédent
        $nbCommandesMois     = CommandeFournisseur::where('statut', '!=', CommandeFournisseur::STATUT_ANNULEE)
            ->whereBetween('date_commande', [$startMois, $now])->count();
        $nbCommandesMoisPrec = CommandeFournisseur::where('statut', '!=', CommandeFournisseur::STATUT_ANNULEE)
            ->whereBetween('date_commande', [
                $moisPrec->copy()->startOfMonth(),
                $moisPrec->copy()->endOfMonth(),
            ])->count();
        $tendanceNbCmd = $nbCommandesMoisPrec > 0
            ? round((($nbCommandesMois - $nbCommandesMoisPrec) / $nbCommandesMoisPrec) * 100)
            : ($nbCommandesMois > 0 ? 100 : 0);

        // ── Sparkline : dépenses par mois sur 6 derniers mois
        $depensesMois = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $depensesMois[] = [
                'label'   => $d->locale('fr')->isoFormat('MMM'),
                'montant' => (float) CommandeFournisseur::where('statut', '!=', CommandeFournisseur::STATUT_ANNULEE)
                    ->whereBetween('date_commande', [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()])
                    ->sum('montant_ttc'),
            ];
        }
        $depensesMax = max(array_column($depensesMois, 'montant')) ?: 1;

        // ── Top 5 fournisseurs (12 derniers mois)
        $topFournisseurs = CommandeFournisseur::selectRaw('fournisseur_id, SUM(montant_ttc) as total, COUNT(*) as nb')
            ->where('statut', '!=', CommandeFournisseur::STATUT_ANNULEE)
            ->where('date_commande', '>=', $now->copy()->subMonths(12))
            ->whereNotNull('fournisseur_id')
            ->groupBy('fournisseur_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('fournisseur:id,raison_sociale,nom')
            ->get();
        $topFournisseurMax = $topFournisseurs->max('total') ?: 1;

        // ── Délai moyen de livraison (30 derniers jours)
        $delaiMoyenJours = (float) CommandeFournisseur::whereNotNull('date_livraison_effective')
            ->whereNotNull('date_commande')
            ->where('date_livraison_effective', '>=', $now->copy()->subDays(90))
            ->selectRaw('AVG(date_livraison_effective - date_commande) as m')
            ->value('m') ?? 0;

        // ── DEMANDES INTERNES ─────────────────────────────
        $demandesEnAttenteN1        = CommandeInterne::where('statut', CommandeInterne::STATUT_EN_ATTENTE_N1)->count();
        $demandesTransmises         = CommandeInterne::where('statut', CommandeInterne::STATUT_TRANSMISE_APPRO)->count();
        $demandesLivraisonPartielle = CommandeInterne::where('statut', CommandeInterne::STATUT_LIVREE_PARTIELLE)->count();

        // Demandes en attente > 3 jours = urgence approbation
        $demandesRetard = CommandeInterne::whereIn('statut', [
                CommandeInterne::STATUT_EN_ATTENTE_N1,
                CommandeInterne::STATUT_TRANSMISE_APPRO,
            ])
            ->where('created_at', '<=', $now->copy()->subDays(3))
            ->count();

        $dernieresDemandes = CommandeInterne::with(['demandeur'])
            ->whereIn('statut', [
                CommandeInterne::STATUT_EN_ATTENTE_N1,
                CommandeInterne::STATUT_TRANSMISE_APPRO,
                CommandeInterne::STATUT_LIVREE_PARTIELLE,
            ])
            ->latest()->take(5)->get();

        // ── STOCK ─────────────────────────────────────────
        $produitsTotal   = Produit::count();
        $produitsStock   = Produit::where('est_stockable', true)->count();
        $stockRupture    = Produit::where('est_stockable', true)
            ->where('stock_actuel', '<=', 0)
            ->count();
        $stockAlerte     = Produit::where('est_stockable', true)
            ->where('stock_actuel', '>', 0)
            ->whereRaw('stock_actuel <= COALESCE(seuil_alerte, stock_minimum, 0)')
            ->count();
        $stockOk         = max(0, $produitsStock - $stockRupture - $stockAlerte);
        $stockTotal      = max(1, $produitsStock);

        // Valorisation stock : SUM(stock_actuel * prix_unitaire)
        $valeurStock = (float) Produit::where('est_stockable', true)
            ->selectRaw('COALESCE(SUM(stock_actuel * COALESCE(prix_unitaire,0)), 0) as v')
            ->value('v');

        $articlesRupture = Produit::where('est_stockable', true)
            ->whereRaw('stock_actuel <= COALESCE(seuil_alerte, stock_minimum, 0)')
            ->orderBy('stock_actuel')
            ->get(['id', 'designation', 'stock_actuel', 'seuil_alerte', 'stock_minimum', 'unite_mesure']);

        $totalFournisseurs = ContactOrganisation::where('type', 'fournisseur')->count();

        // ── CONTRATS ──────────────────────────────────────
        $contratsActifs           = ContratFournisseur::actifs()->count();
        $contratsProcheExpiration = ContratFournisseur::procheExpiration(60)->count();
        $contratsExpirentBientot  = ContratFournisseur::actifs()
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [$now->startOfDay(), $now->copy()->addDays(60)->endOfDay()])
            ->with('fournisseur:id,raison_sociale,nom')
            ->orderBy('date_fin')
            ->take(5)
            ->get();

        // ── FACTURES ──────────────────────────────────────
        $facturesAOrdonnancer = Facture::whereNull('ordonnancee_at')->count();
        $facturesAPayer       = Facture::whereNotNull('ordonnancee_at')
            ->whereRaw('COALESCE(montant_regle, 0) < COALESCE(montant_ttc, 0)')
            ->count();
        $facturesEchues       = Facture::whereNotNull('date_echeance')
            ->whereDate('date_echeance', '<', $now)
            ->whereRaw('COALESCE(montant_regle, 0) < COALESCE(montant_ttc, 0)')
            ->count();
        $montantAPayer        = (float) Facture::whereNotNull('ordonnancee_at')
            ->whereRaw('COALESCE(montant_regle, 0) < COALESCE(montant_ttc, 0)')
            ->selectRaw('COALESCE(SUM(montant_ttc - COALESCE(montant_regle,0)), 0) as v')
            ->value('v');

        // ── MOYENS GÉNÉRAUX ───────────────────────────────
        $ticketsSignales     = Dysfonctionnement::where('statut', Dysfonctionnement::STATUT_SIGNALE)->count();
        $ticketsEnTraitement = Dysfonctionnement::where('statut', Dysfonctionnement::STATUT_PRIS_EN_CHARGE)->count();
        $ticketsResolus      = Dysfonctionnement::where('statut', Dysfonctionnement::STATUT_RESOLU)->count();
        $derniersTickets     = Dysfonctionnement::with(['declarant'])
            ->whereIn('statut', [Dysfonctionnement::STATUT_SIGNALE, Dysfonctionnement::STATUT_PRIS_EN_CHARGE])
            ->latest()->take(5)->get();

        $interventionsPlanifiees = Intervention::where('statut', Intervention::STATUT_PLANIFIEE)->count();
        $interventionsEnCours    = Intervention::where('statut', Intervention::STATUT_EN_COURS)->count();
        $interventionsTerminees  = Intervention::where('statut', Intervention::STATUT_TERMINEE)->count();

        $immobilisationsTotal = Immobilisation::count();

        return view('appro.dashboard', compact(
            // Achats & finance
            'commandesEnCours', 'commandesTotal', 'derniersCommandes',
            'montantMois', 'montantMoisPrec', 'tendanceMontant',
            'nbCommandesMois', 'nbCommandesMoisPrec', 'tendanceNbCmd',
            'depensesMois', 'depensesMax',
            'topFournisseurs', 'topFournisseurMax',
            'delaiMoyenJours',
            // Demandes internes
            'demandesEnAttenteN1', 'demandesTransmises', 'demandesLivraisonPartielle',
            'demandesRetard', 'dernieresDemandes',
            // Stock
            'produitsTotal', 'produitsStock',
            'stockOk', 'stockAlerte', 'stockRupture', 'stockTotal',
            'valeurStock', 'articlesRupture', 'totalFournisseurs',
            // Contrats
            'contratsActifs', 'contratsProcheExpiration', 'contratsExpirentBientot',
            // Factures
            'facturesAOrdonnancer', 'facturesAPayer', 'facturesEchues', 'montantAPayer',
            // MG
            'ticketsSignales', 'ticketsEnTraitement', 'ticketsResolus', 'derniersTickets',
            'interventionsPlanifiees', 'interventionsEnCours', 'interventionsTerminees',
            'immobilisationsTotal',
        ));
    }

    /**
     * Endpoint AJAX — vérification périodique des ruptures de stock
     * pour les gestionnaires Achats/MG et admins (polling toutes les 5 min).
     */
    public function stockRuptureCheck()
    {
        $articles = Produit::where('est_stockable', true)
            ->whereRaw('stock_actuel <= COALESCE(seuil_alerte, stock_minimum, 0)')
            ->orderBy('stock_actuel')
            ->get(['id', 'designation', 'stock_actuel', 'seuil_alerte', 'stock_minimum', 'unite_mesure'])
            ->map(function ($a) {
                $seuil = (float) ($a->seuil_alerte ?? $a->stock_minimum ?? 0);
                return [
                    'id'          => $a->id,
                    'designation' => $a->designation,
                    'stock'       => (float) $a->stock_actuel,
                    'seuil'       => $seuil,
                    'unite'       => $a->unite_mesure,
                    'rupture'     => (float) $a->stock_actuel <= 0,
                    'url_edit'    => route('referentiel.catalogue.edit', $a->id),
                ];
            });

        return response()->json([
            'count'       => $articles->count(),
            'articles'    => $articles,
            'commande_url'=> route('appro.commandes.create'),
            'checked_at'  => now()->toIso8601String(),
        ]);
    }
}
