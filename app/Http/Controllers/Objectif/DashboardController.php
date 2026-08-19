<?php

namespace App\Http\Controllers\Objectif;

use App\Http\Controllers\Controller;
use App\Models\Intranet\Evaluation;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\Objectif;
use App\Services\Objectif\AlertesService;

class DashboardController extends Controller
{
    public function index(AlertesService $alertes)
    {
        $objectifs = Objectif::with(['kpi', 'sousObjectifs', 'projets', 'responsable'])->get();
        $kpis      = Kpi::with('objectif')->get();

        $stats = [
            'objectifs_total'       => $objectifs->count(),
            'objectifs_actifs'      => $objectifs->where('statut', 'actif')->count(),
            'objectifs_atteints'    => $objectifs->where('statut', 'atteint')->count(),
            'objectifs_en_retard'   => $objectifs->filter(fn($o) => $o->estEnRetard())->count(),
            'objectifs_strategiques'=> $objectifs->whereNull('parent_id')->count(),
            'kpi_total'             => $kpis->count(),
            'kpi_au_dessus_cible'   => $kpis->filter(fn($k) => $k->valeur_cible && $k->valeur_actuelle >= $k->valeur_cible)->count(),
            'kpi_en_alerte'         => $kpis->filter(fn($k) => $k->valeur_cible && $k->valeur_actuelle < $k->valeur_cible * 0.5)->count(),
            'evaluations_total'     => Evaluation::count(),
            'evaluations_finalisees'=> Evaluation::where('statut', 'finalise')->count(),
        ];

        // Top objectifs stratégiques avec progression
        $objectifsStrategiques = $objectifs->whereNull('parent_id')->sortByDesc('progression')->take(8);

        // KPI critiques (< 50% atteinte)
        $kpisCritiques = $kpis->filter(fn($k) => $k->valeur_cible && $k->progression < 50)
                              ->sortBy('progression')
                              ->take(5);

        // KPI au top (>= 90%)
        $kpisAuTop = $kpis->filter(fn($k) => $k->valeur_cible && $k->progression >= 90)
                          ->sortByDesc('progression')
                          ->take(5);

        // Répartition par portée
        $repartitionPortee = $objectifs->groupBy('portee')->map->count();

        // Répartition par statut
        $repartitionStatut = $objectifs->groupBy('statut')->map->count();

        // Alertes stratégiques (objectifs en retard, KPI rouges, plans retardés…)
        $alertesListe    = $alertes->alertes()->take(10);
        $compteurAlertes = $alertes->compteurs();

        return view('objectif.dashboard', compact(
            'stats', 'objectifsStrategiques', 'kpisCritiques', 'kpisAuTop',
            'repartitionPortee', 'repartitionStatut',
            'alertesListe', 'compteurAlertes'
        ));
    }
}
