<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Exercice;
use App\Models\CommandeFournisseur;
use App\Models\Absence;
use App\Models\Dysfonctionnement;
use App\Models\Intranet\Annonce;
use App\Models\Intranet\Courrier;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\News;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\Projet;
use App\Models\Intranet\Ressource;
use App\Models\Intranet\Service;
use App\Models\Intranet\Tache;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // ── INTRANET ────────────────────────────────────────────────
        $totalAnnonces       = Annonce::count();
        $annoncesUrgentes    = Annonce::where('is_urgent', true)->count();
        $dernieresAnnonces   = Annonce::with('auteur')->latest()->take(5)->get();

        $dernieresNews       = News::with('auteur')->latest()->take(3)->get();

        $evenementsAVenir    = Evenement::aVenir()->count();
        $prochainsEvenements = Evenement::with('type')
            ->aVenir()
            ->orderBy('date_debut')
            ->take(5)
            ->get();

        $projetsEnCours      = Projet::whereHas('statut', fn($q) => $q->where('libelle', 'En cours'))->count();
        $totalProjets        = Projet::count();
        $derniersProjets     = Projet::with(['statut', 'priorite'])->latest()->take(4)->get();

        $tachesEnCours       = Tache::whereHas('statut', fn($q) => $q->whereIn('libelle', ['En cours', 'Non démarré']))->count();
        $tachesUrgentes      = Tache::whereHas('priorite', fn($q) => $q->where('libelle', 'Urgente'))->count();

        $courriersEnAttente  = Courrier::enAttente()->count();

        $totalDocuments      = Ressource::count();

        // ── ERP — données compactes pour les cartes modules ─────────
        $exerciceActif         = Exercice::where('statut', 1)->latest()->first();
        $budgetGlobal          = $exerciceActif?->budgetglobalinitial ?? 0;
        $totalEmployees        = Employee::where('statut', 1)->count();
        // Statuts legacy ERP stockés en integer (1 = en attente / en cours / ouvert)
        $absencesEnCours       = Absence::where('statut', 1)->count();
        $commandesEnCours      = CommandeFournisseur::where('statut', 1)->count();
        $dysfonctionnementsOuverts = Dysfonctionnement::where('statut', 1)->count();

        // ── ANNUAIRE ────────────────────────────────────────────────
        $totalCollaborateurs = User::actif()->count();
        $totalServices       = Service::actif()->count();
        $totalEquipes        = Groupe::count();

        // ── OBJECTIFS & KPI ─────────────────────────────────────────
        $objectifsActifs    = Objectif::actif()->count();
        $objectifsAtteints  = Objectif::where('statut', 'atteint')->count();
        $kpiTotal           = Kpi::count();
        $kpisCritiques      = Kpi::all()
            ->filter(fn($k) => $k->valeur_cible && $k->progression < 50)
            ->take(3);
        $topObjectifs       = Objectif::with('responsable')
            ->whereNull('parent_id')
            ->where('statut', 'actif')
            ->take(3)
            ->get();

        return view('dashboard', compact(
            'user',
            // Intranet
            'totalAnnonces', 'annoncesUrgentes', 'dernieresAnnonces',
            'dernieresNews',
            'evenementsAVenir', 'prochainsEvenements',
            'projetsEnCours', 'totalProjets', 'derniersProjets',
            'tachesEnCours', 'tachesUrgentes',
            'courriersEnAttente',
            'totalDocuments',
            // ERP (modules)
            'exerciceActif', 'budgetGlobal',
            'totalEmployees', 'absencesEnCours',
            'commandesEnCours', 'dysfonctionnementsOuverts',
            // Annuaire
            'totalCollaborateurs', 'totalServices', 'totalEquipes',
            // Objectifs & KPI
            'objectifsActifs', 'objectifsAtteints', 'kpiTotal',
            'kpisCritiques', 'topObjectifs',
        ));
    }
}
