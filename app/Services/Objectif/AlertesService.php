<?php

namespace App\Services\Objectif;

use App\Models\Intranet\Kpi;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\PlanAction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service d'analyse stratégique : détecte les anomalies sur les objectifs, KPI et plans d'action.
 * Utilisé par le dashboard et le widget « Alertes » de la page d'accueil RH.
 */
class AlertesService
{
    /**
     * Construit la liste complète des alertes, triées par sévérité décroissante.
     */
    public function alertes(): Collection
    {
        return collect()
            ->concat($this->objectifsEnRetard())
            ->concat($this->objectifsCriticalDeadline())
            ->concat($this->kpiEnZoneRouge())
            ->concat($this->kpiSansMesureRecente())
            ->concat($this->plansActionEnRetard())
            ->sortByDesc('severite')
            ->values();
    }

    /**
     * Objectifs actifs dont la date_fin est dépassée.
     */
    public function objectifsEnRetard(): Collection
    {
        return Objectif::where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '<', now())
            ->with('responsable')
            ->get()
            ->map(fn($o) => [
                'type'        => 'objectif_retard',
                'severite'    => 3, // critique
                'titre'       => $o->titre,
                'message'     => 'Objectif en retard depuis ' . $o->date_fin->diffForHumans(['parts' => 1, 'short' => true]),
                'url'         => route('objectifs.objectifs.show', $o),
                'responsable' => $o->responsable?->name,
                'date'        => $o->date_fin,
                'icone'       => 'fa-bullseye',
                'couleur'     => '#DC2626',
            ]);
    }

    /**
     * Objectifs actifs dont la date_fin arrive dans moins de 14 jours.
     */
    public function objectifsCriticalDeadline(int $joursAvantEcheance = 14): Collection
    {
        return Objectif::where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '>=', now())
            ->whereDate('date_fin', '<=', now()->addDays($joursAvantEcheance))
            ->with('responsable')
            ->get()
            ->map(fn($o) => [
                'type'        => 'objectif_imminent',
                'severite'    => 2, // attention
                'titre'       => $o->titre,
                'message'     => 'Échéance dans ' . $o->date_fin->diffForHumans(['parts' => 1, 'short' => true]) . ' · Progression ' . $o->progression . '%',
                'url'         => route('objectifs.objectifs.show', $o),
                'responsable' => $o->responsable?->name,
                'date'        => $o->date_fin,
                'icone'       => 'fa-clock',
                'couleur'     => '#F59E0B',
            ]);
    }

    /**
     * KPI avec une cible définie et une progression < 50% (zone rouge).
     */
    public function kpiEnZoneRouge(): Collection
    {
        return Kpi::with('objectif')
            ->whereNotNull('valeur_cible')
            ->where('valeur_cible', '>', 0)
            ->get()
            ->filter(fn($k) => $k->progression < 50)
            ->map(fn($k) => [
                'type'        => 'kpi_rouge',
                'severite'    => 3,
                'titre'       => $k->titre,
                'message'     => 'KPI à ' . $k->progression . '% de la cible (' . rtrim(rtrim((string)$k->valeur_actuelle, '0'), '.') . ' / ' . rtrim(rtrim((string)$k->valeur_cible, '0'), '.') . ' ' . ($k->unite ?? '') . ')',
                'url'         => route('objectifs.kpi.show', $k),
                'responsable' => $k->objectif?->responsable?->name,
                'date'        => $k->updated_at,
                'icone'       => 'fa-chart-line',
                'couleur'     => '#DC2626',
            ])->values();
    }

    /**
     * KPI sans nouvelle mesure depuis trop longtemps (selon périodicité).
     */
    public function kpiSansMesureRecente(): Collection
    {
        return Kpi::with('valeurs')
            ->get()
            ->filter(function ($k) {
                $derniere = $k->valeurs->sortByDesc('date_mesure')->first();
                if (!$derniere) return true; // jamais mesuré
                $seuilJours = match ($k->periodicite) {
                    'quotidien'   => 3,
                    'hebdo'       => 14,
                    'mensuel'     => 45,
                    'trimestriel' => 100,
                    'annuel'      => 400,
                    default       => 60,
                };
                return Carbon::parse($derniere->date_mesure)->lt(now()->subDays($seuilJours));
            })
            ->map(fn($k) => [
                'type'        => 'kpi_sans_mesure',
                'severite'    => 1, // info
                'titre'       => $k->titre,
                'message'     => $k->valeurs->isEmpty()
                    ? 'Aucune mesure saisie'
                    : 'Dernière mesure le ' . Carbon::parse($k->valeurs->sortByDesc('date_mesure')->first()->date_mesure)->translatedFormat('d M Y'),
                'url'         => route('objectifs.kpi.show', $k),
                'responsable' => null,
                'date'        => $k->updated_at,
                'icone'       => 'fa-bell',
                'couleur'     => '#0891B2',
            ])->values();
    }

    /**
     * Plans d'action en retard.
     */
    public function plansActionEnRetard(): Collection
    {
        return PlanAction::with(['responsable', 'objectif'])
            ->whereNotIn('statut', ['realise', 'annule'])
            ->whereNotNull('date_echeance')
            ->whereDate('date_echeance', '<', now())
            ->get()
            ->map(fn($p) => [
                'type'        => 'plan_retard',
                'severite'    => 2,
                'titre'       => $p->titre,
                'message'     => 'Plan en retard de ' . $p->date_echeance->diffForHumans(['parts' => 1, 'short' => true]) . ' · ' . ($p->avancement ?? 0) . '%',
                'url'         => route('objectifs.plans-action.show', $p),
                'responsable' => $p->responsable?->name,
                'date'        => $p->date_echeance,
                'icone'       => 'fa-bolt',
                'couleur'     => '#F59E0B',
            ]);
    }

    /**
     * Compteurs synthétiques pour widget header / dashboard.
     */
    public function compteurs(): array
    {
        $alertes = $this->alertes();
        return [
            'total'       => $alertes->count(),
            'critiques'   => $alertes->where('severite', 3)->count(),
            'attention'   => $alertes->where('severite', 2)->count(),
            'info'        => $alertes->where('severite', 1)->count(),
        ];
    }
}
