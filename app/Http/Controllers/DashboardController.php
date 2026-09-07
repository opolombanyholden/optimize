<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Exercice;
use App\Models\CommandeFournisseur;
use App\Models\CommandeInterne;
use App\Models\Absence;
use App\Models\Dysfonctionnement;
use App\Models\Intervention;
use App\Models\Intranet\Annonce;
use App\Models\Intranet\Courrier;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\Groupe;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\Media;
use App\Models\Intranet\News;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\Opportunite;
use App\Models\Intranet\Projet;
use App\Models\Intranet\Publication;
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
        $derniersCourriers   = Courrier::enAttente()
            ->orderByDesc('date_reception')
            ->take(4)
            ->get();

        $totalDocuments      = Ressource::count();

        // ── ERP — données compactes pour les cartes modules ─────────
        $exerciceActif         = Exercice::where('statut', 1)->latest()->first();
        $budgetGlobal          = $exerciceActif?->budgetglobalinitial ?? 0;
        $totalEmployees        = Employee::where('statut', 1)->count();
        // Statuts legacy ERP stockés en integer (1 = en attente / en cours / ouvert)
        $absencesEnCours       = Absence::where('statut', 1)->count();
        $commandesEnCours      = CommandeFournisseur::where('statut', 1)->count();
        $dysfonctionnementsOuverts = Dysfonctionnement::where('statut', 1)->count();

        // ── ACHATS & MOYENS GÉNÉRAUX (bloc dédié dashboard) ─────────
        $demandesInternesEnAttente = CommandeInterne::whereIn('statut', [
            CommandeInterne::STATUT_EN_ATTENTE_N1,
            CommandeInterne::STATUT_TRANSMISE_APPRO,
            CommandeInterne::STATUT_LIVREE_PARTIELLE,
        ])->count();
        $dysfonctionnementsSignales    = Dysfonctionnement::where('statut', Dysfonctionnement::STATUT_SIGNALE)->count();
        $dysfonctionnementsEnTraitement = Dysfonctionnement::where('statut', Dysfonctionnement::STATUT_PRIS_EN_CHARGE)->count();
        $interventionsEnCours          = Intervention::where('statut', Intervention::STATUT_EN_COURS)->count();

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

        // ── FIL SOCIAL ──────────────────────────────────────────────
        $today = now()->startOfDay();
        $in7   = now()->addDays(7)->endOfDay();
        $anniversairesSemaine = Employee::with('user')
            ->where('statut', 1)
            ->whereNotNull('date_naissance')
            ->get()
            ->filter(function ($e) use ($today, $in7) {
                $bday = $e->date_naissance->copy()->year($today->year);
                if ($bday->lt($today)) $bday->addYear();
                return $bday->between($today, $in7);
            })
            ->sortBy(function ($e) use ($today) {
                $bday = $e->date_naissance->copy()->year($today->year);
                if ($bday->lt($today)) $bday->addYear();
                return $bday->timestamp;
            })
            ->take(5)
            ->values();

        // Vœux : compteur reçu par employé + set des IDs déjà souhaités par l'utilisateur courant
        $wishesData = [];
        $wishesDejaEnvoyes = collect();
        if ($anniversairesSemaine->isNotEmpty()) {
            $bdayDates = $anniversairesSemaine->map(function ($e) use ($today) {
                $bday = $e->date_naissance->copy()->year($today->year);
                if ($bday->lt($today)) $bday->addYear();
                return $bday->toDateString();
            });
            $counts = \App\Models\BirthdayWish::whereIn('employee_id', $anniversairesSemaine->pluck('id'))
                ->whereIn('date_anniversaire', $bdayDates->unique())
                ->selectRaw('employee_id, count(*) as total')
                ->groupBy('employee_id')
                ->pluck('total', 'employee_id');
            $wishesData = $counts->toArray();

            if ($user) {
                $wishesDejaEnvoyes = \App\Models\BirthdayWish::where('expediteur_id', $user->id)
                    ->whereIn('employee_id', $anniversairesSemaine->pluck('id'))
                    ->whereIn('date_anniversaire', $bdayDates->unique())
                    ->pluck('employee_id');
            }
        }

        // ── CRM — suivi opportunités ────────────────────────────────
        $totalOpportunitesOuvertes = Opportunite::where('statut', 'ouvert')->count();
        $valeurPipeline            = (float) Opportunite::where('statut', 'ouvert')->sum('valeur');
        $opportunitesGagneesMois   = Opportunite::where('statut', 'gagnee')
            ->whereBetween('date_cloture_reelle', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $topOpportunites           = Opportunite::with(['organisation', 'etape'])
            ->where('statut', 'ouvert')
            ->orderByDesc('valeur')
            ->take(4)
            ->get();

        // ── MÉDIATHÈQUE — dernières photos / vidéos ─────────────────
        // Médias : publics OU appartenant à l'utilisateur courant
        $derniersMedias = Media::whereIn('type', ['image', 'video'])
            ->where(function ($q) use ($user) {
                $q->where('is_public', true);
                if ($user) $q->orWhere('created_by', $user->id);
            })
            ->with('auteur')
            ->latest()
            ->take(12)
            ->get();

        $publicationsPubliques = Publication::with(['publishable', 'auteur'])
            ->where('visibilite', 'public')
            ->whereNotNull('publie_le')
            ->orderByDesc('publie_le')
            ->take(5)
            ->get()
            ->filter(fn($p) => $p->publishable !== null)
            ->values();

        return view('dashboard', compact(
            'user',
            // Intranet
            'totalAnnonces', 'annoncesUrgentes', 'dernieresAnnonces',
            'dernieresNews',
            'evenementsAVenir', 'prochainsEvenements',
            'projetsEnCours', 'totalProjets', 'derniersProjets',
            'tachesEnCours', 'tachesUrgentes',
            'courriersEnAttente', 'derniersCourriers',
            'totalDocuments',
            // ERP (modules)
            'exerciceActif', 'budgetGlobal',
            'totalEmployees', 'absencesEnCours',
            'commandesEnCours', 'dysfonctionnementsOuverts',
            'demandesInternesEnAttente', 'dysfonctionnementsSignales',
            'dysfonctionnementsEnTraitement', 'interventionsEnCours',
            // Annuaire
            'totalCollaborateurs', 'totalServices', 'totalEquipes',
            // Objectifs & KPI
            'objectifsActifs', 'objectifsAtteints', 'kpiTotal',
            'kpisCritiques', 'topObjectifs',
            // Fil social
            'anniversairesSemaine', 'publicationsPubliques',
            'wishesData', 'wishesDejaEnvoyes',
            // CRM
            'totalOpportunitesOuvertes', 'valeurPipeline',
            'opportunitesGagneesMois', 'topOpportunites',
            // Médiathèque
            'derniersMedias',
        ));
    }
}
