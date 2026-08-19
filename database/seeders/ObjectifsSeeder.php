<?php

namespace Database\Seeders;

use App\Models\Intranet\Evaluation;
use App\Models\Intranet\Kpi;
use App\Models\Intranet\KpiValeur;
use App\Models\Intranet\Objectif;
use App\Models\Intranet\PlanAction;
use App\Models\Intranet\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ObjectifsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@optimize.local')->first();
        if (! $admin) {
            $this->command->warn('AnnuaireSeeder doit être lancé avant ObjectifsSeeder.');
            return;
        }

        $userIdBy = User::pluck('id', 'email');
        $serviceIdBy = Service::pluck('id', 'code');

        // ── Objectifs stratégiques (Organisation) ──
        $strategiques = [
            [
                'code'        => 'OKR-2026-01',
                'titre'       => 'Devenir leader de l\'ERP en Afrique Centrale',
                'description' => 'Atteindre une part de marché de 15% sur le marché de l\'ERP en Afrique Centrale d\'ici fin 2026.',
                'portee'      => 'organisation',
                'icone'       => 'fa-trophy', 'couleur' => '#DB2777',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif',
                'responsable' => 'admin@optimize.local',
                'ponderation' => 40,
            ],
            [
                'code'        => 'OKR-2026-02',
                'titre'       => 'Excellence opérationnelle et satisfaction client',
                'description' => 'Atteindre un NPS > 50 et un taux de churn < 5% sur l\'année.',
                'portee'      => 'organisation',
                'icone'       => 'fa-handshake', 'couleur' => '#16A34A',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif',
                'responsable' => 'aminata.mensah@optimize.local',
                'ponderation' => 30,
            ],
            [
                'code'        => 'OKR-2026-03',
                'titre'       => 'Attirer et fidéliser les meilleurs talents',
                'description' => 'Réduire le turnover < 10% et atteindre 90% de satisfaction interne.',
                'portee'      => 'organisation',
                'icone'       => 'fa-users-rays', 'couleur' => '#F59E0B',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif',
                'responsable' => 'aminata.mensah@optimize.local',
                'ponderation' => 30,
            ],
        ];

        $strategiquesById = [];
        foreach ($strategiques as $sd) {
            $obj = Objectif::firstOrCreate(
                ['code' => $sd['code']],
                array_merge(
                    collect($sd)->except('responsable')->toArray(),
                    [
                        'responsable_id' => $userIdBy[$sd['responsable']] ?? null,
                        'created_by'     => $admin->id,
                    ]
                )
            );
            $strategiquesById[$sd['code']] = $obj;
        }

        // ── Objectifs opérationnels (sous-objectifs) ──
        $operationnels = [
            // Sous OKR-2026-01 (Leader ERP)
            [
                'parent_code' => 'OKR-2026-01',
                'code'        => 'OPS-26-01-A',
                'titre'       => 'Acquérir 50 nouveaux clients ERP',
                'portee'      => 'service', 'service_code' => 'COM',
                'icone'       => 'fa-user-plus', 'couleur' => '#DB2777',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif', 'ponderation' => 50,
                'responsable' => 'linda.bouanga@optimize.local',
            ],
            [
                'parent_code' => 'OKR-2026-01',
                'code'        => 'OPS-26-01-B',
                'titre'       => 'Lancer 3 nouveaux modules ERP',
                'portee'      => 'service', 'service_code' => 'DSI',
                'icone'       => 'fa-rocket', 'couleur' => '#0D9488',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif', 'ponderation' => 50,
                'responsable' => 'patrick.nguema@optimize.local',
            ],
            // Sous OKR-2026-02 (Satisfaction client)
            [
                'parent_code' => 'OKR-2026-02',
                'code'        => 'OPS-26-02-A',
                'titre'       => 'Mettre en place une équipe Customer Success',
                'portee'      => 'service', 'service_code' => 'COM',
                'icone'       => 'fa-headset', 'couleur' => '#16A34A',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-06-30',
                'statut'      => 'actif', 'ponderation' => 40,
                'responsable' => 'sylvie.mba@optimize.local',
            ],
            [
                'parent_code' => 'OKR-2026-02',
                'code'        => 'OPS-26-02-B',
                'titre'       => 'Réduire le temps de réponse support à < 4h',
                'portee'      => 'service', 'service_code' => 'DSI',
                'icone'       => 'fa-stopwatch', 'couleur' => '#0891B2',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif', 'ponderation' => 60,
                'responsable' => 'patrick.nguema@optimize.local',
            ],
            // Sous OKR-2026-03 (Talents)
            [
                'parent_code' => 'OKR-2026-03',
                'code'        => 'OPS-26-03-A',
                'titre'       => 'Plan de formation continue pour 100% des collaborateurs',
                'portee'      => 'service', 'service_code' => 'RH',
                'icone'       => 'fa-graduation-cap', 'couleur' => '#F59E0B',
                'date_debut'  => '2026-01-01', 'date_fin' => '2026-12-31',
                'statut'      => 'actif', 'ponderation' => 50,
                'responsable' => 'aminata.mensah@optimize.local',
            ],
        ];

        foreach ($operationnels as $od) {
            $parent = $strategiquesById[$od['parent_code']] ?? null;
            if (! $parent) continue;
            Objectif::firstOrCreate(
                ['code' => $od['code']],
                [
                    'titre'         => $od['titre'],
                    'description'   => $od['titre'] . ' — décliné depuis ' . $parent->titre,
                    'parent_id'     => $parent->id,
                    'portee'        => $od['portee'],
                    'service_id'    => $serviceIdBy[$od['service_code']] ?? null,
                    'responsable_id'=> $userIdBy[$od['responsable']] ?? null,
                    'date_debut'    => $od['date_debut'],
                    'date_fin'      => $od['date_fin'],
                    'statut'        => $od['statut'],
                    'icone'         => $od['icone'],
                    'couleur'       => $od['couleur'],
                    'ponderation'   => $od['ponderation'],
                    'created_by'    => $admin->id,
                ]
            );
        }

        // ── KPI ──
        $kpis = [
            // KPI sur OKR-2026-01
            ['objectif_code' => 'OKR-2026-01', 'titre' => 'Nouveaux clients ERP signés',  'unite' => 'clients', 'cible' => 50, 'actuelle' => 18, 'periode' => 'mensuel'],
            ['objectif_code' => 'OKR-2026-01', 'titre' => 'Chiffre d\'affaires ERP',      'unite' => 'M XAF',  'cible' => 850, 'actuelle' => 312, 'periode' => 'trimestriel'],
            ['objectif_code' => 'OPS-26-01-B', 'titre' => 'Nombre de modules livrés',     'unite' => 'modules','cible' => 3, 'actuelle' => 1, 'periode' => 'trimestriel'],

            // KPI sur OKR-2026-02
            ['objectif_code' => 'OKR-2026-02', 'titre' => 'Net Promoter Score (NPS)',     'unite' => 'pts', 'cible' => 50, 'actuelle' => 42, 'periode' => 'trimestriel'],
            ['objectif_code' => 'OKR-2026-02', 'titre' => 'Taux de churn annuel',         'unite' => '%',   'cible' => 5,  'actuelle' => 7.2, 'periode' => 'mensuel'],
            ['objectif_code' => 'OPS-26-02-B', 'titre' => 'Temps moyen de réponse support', 'unite' => 'h', 'cible' => 4, 'actuelle' => 6.5, 'periode' => 'mensuel'],

            // KPI sur OKR-2026-03
            ['objectif_code' => 'OKR-2026-03', 'titre' => 'Taux de turnover',             'unite' => '%',   'cible' => 10, 'actuelle' => 8.5, 'periode' => 'trimestriel'],
            ['objectif_code' => 'OKR-2026-03', 'titre' => 'Score satisfaction interne',   'unite' => '/100','cible' => 90, 'actuelle' => 78, 'periode' => 'trimestriel'],
            ['objectif_code' => 'OPS-26-03-A', 'titre' => 'Taux de couverture formation', 'unite' => '%',   'cible' => 100,'actuelle' => 60, 'periode' => 'trimestriel'],

            // KPI transverse (sans objectif)
            ['objectif_code' => null, 'titre' => 'Taux d\'absentéisme global',           'unite' => '%',   'cible' => 3,  'actuelle' => 4.1, 'periode' => 'mensuel'],
        ];

        foreach ($kpis as $kd) {
            $objectifId = $kd['objectif_code'] ? Objectif::where('code', $kd['objectif_code'])->value('id') : null;
            $kpi = Kpi::firstOrCreate(
                ['titre' => $kd['titre']],
                [
                    'description'     => null,
                    'objectif_id'     => $objectifId,
                    'valeur_cible'    => $kd['cible'],
                    'valeur_actuelle' => $kd['actuelle'],
                    'unite'           => $kd['unite'],
                    'periodicite'     => $kd['periode'],
                    'tendance'        => 'stable',
                    'created_by'      => $admin->id,
                ]
            );

            // 3 valeurs historiques pour chaque KPI
            $valeursHistoriques = [
                ['date' => now()->subMonths(3)->toDateString(), 'valeur' => $kd['actuelle'] * 0.6],
                ['date' => now()->subMonths(2)->toDateString(), 'valeur' => $kd['actuelle'] * 0.8],
                ['date' => now()->subMonths(1)->toDateString(), 'valeur' => $kd['actuelle'] * 0.95],
                ['date' => now()->toDateString(),               'valeur' => $kd['actuelle']],
            ];
            foreach ($valeursHistoriques as $vh) {
                KpiValeur::firstOrCreate(
                    ['kpi_id' => $kpi->id, 'date_mesure' => $vh['date']],
                    ['valeur' => round($vh['valeur'], 2), 'created_by' => $admin->id]
                );
            }

            // Tendance basée sur dernières valeurs
            $kpi->update(['tendance' => 'hausse']);
        }

        // ── Évaluations ──
        $evaluationsData = [
            ['titre' => 'Évaluation Q1 2026 — Patrick Nguema', 'user' => 'patrick.nguema@optimize.local', 'evaluateur' => 'admin@optimize.local',           'objectif_code' => 'OPS-26-01-B', 'score' => 78, 'statut' => 'finalise', 'date' => now()->subWeeks(2)->toDateString(), 'pf' => 'Très bonne maîtrise technique. Anticipation des risques.', 'axes' => 'Améliorer la communication transverse avec les autres services.'],
            ['titre' => 'Évaluation Q1 2026 — Linda Bouanga',  'user' => 'linda.bouanga@optimize.local',   'evaluateur' => 'admin@optimize.local',           'objectif_code' => 'OPS-26-01-A', 'score' => 65, 'statut' => 'finalise', 'date' => now()->subWeeks(2)->toDateString(), 'pf' => 'Forte créativité, bonnes idées de campagnes.', 'axes' => 'Pipeline de leads à industrialiser.'],
            ['titre' => 'Évaluation 6 mois — Sylvie Mba',      'user' => 'sylvie.mba@optimize.local',      'evaluateur' => 'linda.bouanga@optimize.local',   'objectif_code' => 'OPS-26-02-A', 'score' => 82, 'statut' => 'valide',   'date' => now()->subMonth()->toDateString(),  'pf' => 'Excellente intégration. Premiers retours clients très positifs.', 'axes' => null],
            ['titre' => 'Évaluation Q1 — JP Obame',            'user' => 'jp.obame@optimize.local',        'evaluateur' => 'admin@optimize.local',           'objectif_code' => null,           'score' => 72, 'statut' => 'brouillon','date' => now()->subWeek()->toDateString(),   'pf' => 'Rigueur comptable irréprochable.', 'axes' => 'Plus de proactivité sur la digitalisation des process.'],
        ];

        foreach ($evaluationsData as $ed) {
            $userId       = $userIdBy[$ed['user']] ?? null;
            $evaluateurId = $userIdBy[$ed['evaluateur']] ?? null;
            if (! $userId || ! $evaluateurId) continue;
            $objectifId = $ed['objectif_code'] ? Objectif::where('code', $ed['objectif_code'])->value('id') : null;

            Evaluation::firstOrCreate(
                ['titre' => $ed['titre'], 'user_id' => $userId],
                [
                    'evaluateur_id'     => $evaluateurId,
                    'objectif_id'       => $objectifId,
                    'score'             => $ed['score'],
                    'commentaire'       => null,
                    'points_forts'      => $ed['pf'],
                    'axes_amelioration' => $ed['axes'],
                    'date_evaluation'   => $ed['date'],
                    'statut'            => $ed['statut'],
                ]
            );
        }

        // ── Plans d'action ──
        $plansData = [
            ['objectif_code' => 'OPS-26-01-A', 'titre' => 'Lancer campagne LinkedIn ciblée DSI',     'statut' => 'en_cours', 'priorite' => 'haute',   'avancement' => 60, 'budget' => 5_000_000,  'echeance' => '+2 months', 'resp' => 'linda.bouanga@optimize.local'],
            ['objectif_code' => 'OPS-26-01-A', 'titre' => 'Participation salon Africa IT 2026',     'statut' => 'planifie', 'priorite' => 'normale', 'avancement' => 10, 'budget' => 12_000_000, 'echeance' => '+4 months', 'resp' => 'sylvie.mba@optimize.local'],
            ['objectif_code' => 'OPS-26-01-B', 'titre' => 'Module RH avancé (paie + recrutement)',  'statut' => 'en_cours', 'priorite' => 'urgente', 'avancement' => 75, 'budget' => 8_000_000,  'echeance' => '+1 month',  'resp' => 'patrick.nguema@optimize.local'],
            ['objectif_code' => 'OPS-26-02-A', 'titre' => 'Recruter 2 Customer Success Managers',  'statut' => 'planifie', 'priorite' => 'haute',   'avancement' => 0,  'budget' => null,        'echeance' => '+3 months', 'resp' => 'aminata.mensah@optimize.local'],
            ['objectif_code' => 'OPS-26-02-B', 'titre' => 'Mettre en place système de tickets',    'statut' => 'realise',  'priorite' => 'normale', 'avancement' => 100,'budget' => 1_500_000,  'echeance' => '-2 weeks',  'resp' => 'patrick.nguema@optimize.local'],
            ['objectif_code' => 'OPS-26-03-A', 'titre' => 'Plan formation Laravel/Vue 2026',       'statut' => 'en_cours', 'priorite' => 'normale', 'avancement' => 40, 'budget' => 3_500_000,  'echeance' => '+5 months', 'resp' => 'aminata.mensah@optimize.local'],
        ];

        foreach ($plansData as $pd) {
            $objectifId = Objectif::where('code', $pd['objectif_code'])->value('id');
            $userId     = $userIdBy[$pd['resp']] ?? null;
            if (! $objectifId) continue;

            PlanAction::firstOrCreate(
                ['titre' => $pd['titre'], 'objectif_id' => $objectifId],
                [
                    'description'    => 'Plan d\'action concret pour atteindre l\'objectif ' . $pd['objectif_code'] . '.',
                    'responsable_id' => $userId,
                    'statut'         => $pd['statut'],
                    'priorite'       => $pd['priorite'],
                    'avancement'     => $pd['avancement'],
                    'budget_estime'  => $pd['budget'],
                    'date_debut'     => now()->subMonth()->toDateString(),
                    'date_echeance'  => now()->modify($pd['echeance'])->toDateString(),
                    'date_realisation' => $pd['statut'] === 'realise' ? now()->subDays(15)->toDateString() : null,
                    'created_by'     => $admin->id,
                ]
            );
        }

        $this->command->info(sprintf(
            'Objectifs seeded: %d objectifs, %d KPI, %d valeurs, %d évaluations, %d plans d\'action.',
            Objectif::count(),
            Kpi::count(),
            KpiValeur::count(),
            Evaluation::count(),
            PlanAction::count()
        ));
    }
}
