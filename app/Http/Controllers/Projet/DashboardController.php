<?php

namespace App\Http\Controllers\Projet;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Jalon;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetCout;
use App\Models\Intranet\ProjetRisque;
use App\Models\Intranet\Statut;
use App\Models\Intranet\Tache;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $projets = Projet::with(['statut', 'priorite', 'chefProjet'])
            ->withCount(['taches', 'phases', 'membres', 'risques', 'jalons'])
            ->orderByDesc('created_at')
            ->get();

        // KPI globaux
        $stats = [
            'total'      => $projets->count(),
            'en_cours'   => $projets->filter(fn($p) => $p->statut?->libelle === 'En cours')->count(),
            'termines'   => $projets->filter(fn($p) => $p->statut?->libelle === 'Terminé')->count(),
            'en_retard'  => $projets->filter(fn($p) => $p->estEnRetard())->count(),
            'non_demarres' => $projets->filter(fn($p) => $p->statut?->libelle === 'Non démarré')->count(),
        ];

        // Budget global
        $budgetTotal    = $projets->sum('budget_approuve');
        $budgetConsomme = (float) ProjetCout::sum('montant_reel');

        // Tâches globales
        $tachesTotal     = Tache::count();
        $tachesTerminees = Tache::whereHas('statut', fn($s) => $s->where('libelle', 'Terminé'))->count();
        $tachesEnRetard  = Tache::query()->enRetard()->count();
        $tachesUrgentes  = Tache::whereHas('priorite', fn($p) => $p->where('libelle', 'Urgente'))->whereHas('statut', fn($s) => $s->whereNotIn('libelle', ['Terminé', 'Annulé']))->count();

        // Jalons prochains (tous projets, 7 prochains)
        $jalonsProchains = Jalon::with(['projet', 'phase'])
            ->where('statut', 'prevu')
            ->where('date_prevue', '>=', now())
            ->orderBy('date_prevue')
            ->limit(7)
            ->get();

        // Jalons en retard
        $jalonsEnRetard = Jalon::with('projet')
            ->where('statut', 'prevu')
            ->where('date_prevue', '<', now())
            ->count();

        // Risques critiques (tous projets)
        $risquesCritiques = ProjetRisque::with('projet')
            ->whereIn('statut', ['identifie', 'analyse'])
            ->whereRaw('probabilite * impact >= 15')
            ->get();

        // Tâches urgentes en retard (top 10)
        $tachesAlerte = Tache::with(['projet', 'statut', 'priorite', 'responsable'])
            ->enRetard()
            ->orderByDesc('date_fin')
            ->limit(10)
            ->get();

        // Répartition des projets par statut (pour chart)
        $repartitionStatut = $projets->groupBy(fn($p) => $p->statut?->libelle ?? 'Indéfini')
            ->map(fn($group) => $group->count());

        return view('projet.dashboard', compact(
            'projets', 'stats',
            'budgetTotal', 'budgetConsomme',
            'tachesTotal', 'tachesTerminees', 'tachesEnRetard', 'tachesUrgentes',
            'jalonsProchains', 'jalonsEnRetard',
            'risquesCritiques', 'tachesAlerte',
            'repartitionStatut'
        ));
    }

    public function show(Projet $projet)
    {
        $projet->load([
            'statut', 'priorite', 'objectif', 'chefProjet', 'sponsor', 'auteur', 'membres',
            'phases.taches.statut', 'phases.valideurs', 'phases.statut',
            'taches.statut', 'taches.priorite', 'taches.responsable',
            'jalons.valideurs', 'risques', 'problemes', 'changements',
            'valideurs',
        ]);

        $taches       = $projet->taches;
        $tachesCount  = $taches->count();
        // Une tâche est "terminée" si :
        //   - statut libellé "Terminé", OU
        //   - avancement = 100%, OU
        //   - validation approuvée (workflow PMP)
        $tachesTerminees = $taches->filter(fn($t) =>
            $t->statut?->libelle === 'Terminé'
            || (int) $t->avancement === 100
            || $t->statut_validation === 'approuve'
        )->count();
        $tachesEnRetard  = $taches->filter(fn($t) => $t->estEnRetard())->count();

        $kpi = [
            'avancement'        => $projet->avancement_real,
            'taches_total'      => $tachesCount,
            'taches_terminees'  => $tachesTerminees,
            'taches_en_retard'  => $tachesEnRetard,
            'phases'            => $projet->phases->count(),
            'jalons_total'      => $projet->jalons->count(),
            'jalons_atteints'   => $projet->jalons->where('statut', 'atteint')->count(),
            'jalons_en_retard'  => $projet->jalons->filter(fn($j) => $j->estEnRetard())->count(),
            'risques_ouverts'   => $projet->risques->whereIn('statut', ['identifie', 'analyse'])->count(),
            'risques_critiques' => $projet->risques_critiques,
            'problemes_ouverts' => $projet->problemes->where('statut', 'ouvert')->count(),
            'changements_en_attente' => $projet->changements->where('statut', 'soumis')->count(),
            'budget_approuve'   => $projet->budget_approuve,
            'budget_consomme'   => $projet->budget_consomme,
            'budget_restant'    => $projet->budget_restant,
            'heures_estimees'   => $projet->total_heures_estimees,
            'heures_reelles'    => $projet->total_heures_reelles,
            'membres'           => $projet->membres->count(),
        ];

        $tachesRecentes = $taches->sortByDesc('updated_at')->take(5);
        $jalonsProchains = $projet->jalons->where('statut', 'prevu')->sortBy('date_prevue')->take(5);

        // Données pour la validation de clôture
        $users   = \App\Models\User::orderBy('prenoms')->get();
        $groupes = \App\Models\Intranet\Groupe::orderBy('nom')->get();

        // Compteur des clôtures en attente (phases + jalons)
        $phasesEnAttente = $projet->phases->where('statut_cloture', 'soumis')->count();
        $jalonsEnAttente = $projet->jalons->where('statut_cloture', 'soumis')->count();

        return view('projet.show', compact(
            'projet', 'kpi', 'tachesRecentes', 'jalonsProchains',
            'users', 'groupes', 'phasesEnAttente', 'jalonsEnAttente'
        ));
    }
}
