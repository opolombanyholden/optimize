<?php

namespace Database\Seeders;

use App\Models\Intranet\Activite;
use App\Models\Intranet\FeuilleTemps;
use App\Models\Intranet\Jalon;
use App\Models\Intranet\Priorite;
use App\Models\Intranet\Projet;
use App\Models\Intranet\ProjetChangement;
use App\Models\Intranet\ProjetCout;
use App\Models\Intranet\ProjetEvm;
use App\Models\Intranet\ProjetLecon;
use App\Models\Intranet\ProjetLivrable;
use App\Models\Intranet\ProjetPartiePrenante;
use App\Models\Intranet\ProjetPhase;
use App\Models\Intranet\ProjetProbleme;
use App\Models\Intranet\ProjetRessource;
use App\Models\Intranet\ProjetRisque;
use App\Models\Intranet\Statut;
use App\Models\Intranet\Tache;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjetDemoSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $user1 = $users->first();
        $user2 = $users->skip(1)->first() ?? $user1;
        $user3 = $users->skip(2)->first() ?? $user1;

        $statuts   = Statut::pluck('id', 'libelle');
        $priorites = Priorite::pluck('id', 'libelle');

        // ══════════════════════════════════════════════════
        // PROJET 1 : Refonte du SI
        // ══════════════════════════════════════════════════
        $p1 = Projet::updateOrCreate(['code_projet' => 'PRJ-001'], [
            'nom'             => 'Refonte du Système d\'Information',
            'code_projet'     => 'PRJ-001',
            'categorie'       => 'IT',
            'description'     => '<p>Modernisation complète du SI : migration cloud, refonte des applications métier, sécurisation des données.</p>',
            'objectifs'       => '<ul><li>Migrer 100% des services vers le cloud</li><li>Réduire les coûts d\'infrastructure de 30%</li><li>Améliorer le temps de réponse des applications</li></ul>',
            'budget_approuve' => 75000000,
            'devise'          => 'XAF',
            'priorite_id'     => $priorites['Haute'] ?? null,
            'statut_id'       => $statuts['En cours'] ?? null,
            'chef_projet_id'  => $user1->id,
            'sponsor_id'      => $user2->id,
            'created_by'      => $user1->id,
            'date_debut'      => now()->subMonths(3),
            'date_fin'        => now()->addMonths(9),
            'avancement'      => 35,
        ]);

        $p1->membres()->syncWithoutDetaching([$user1->id, $user2->id, $user3->id]);

        // Phases WBS
        $phase1 = ProjetPhase::updateOrCreate(['projet_id' => $p1->id, 'code_wbs' => '1.0'], [
            'nom' => 'Analyse & Cadrage', 'code_wbs' => '1.0', 'ordre' => 1,
            'date_debut' => now()->subMonths(3), 'date_fin' => now()->subMonth(),
            'avancement' => 100, 'couleur' => '#4F46E5', 'responsable_id' => $user1->id, 'created_by' => $user1->id,
        ]);
        $phase2 = ProjetPhase::updateOrCreate(['projet_id' => $p1->id, 'code_wbs' => '2.0'], [
            'nom' => 'Conception technique', 'code_wbs' => '2.0', 'ordre' => 2,
            'date_debut' => now()->subMonth(), 'date_fin' => now()->addMonth(),
            'avancement' => 60, 'couleur' => '#0891B2', 'responsable_id' => $user2->id, 'created_by' => $user1->id,
        ]);
        $phase3 = ProjetPhase::updateOrCreate(['projet_id' => $p1->id, 'code_wbs' => '3.0'], [
            'nom' => 'Développement', 'code_wbs' => '3.0', 'ordre' => 3,
            'date_debut' => now()->addMonth(), 'date_fin' => now()->addMonths(6),
            'avancement' => 10, 'couleur' => '#16A34A', 'responsable_id' => $user3->id, 'created_by' => $user1->id,
        ]);
        $phase4 = ProjetPhase::updateOrCreate(['projet_id' => $p1->id, 'code_wbs' => '4.0'], [
            'nom' => 'Tests & Déploiement', 'code_wbs' => '4.0', 'ordre' => 4,
            'date_debut' => now()->addMonths(6), 'date_fin' => now()->addMonths(9),
            'avancement' => 0, 'couleur' => '#D97706', 'responsable_id' => $user1->id, 'created_by' => $user1->id,
        ]);

        // Tâches
        $taches = [
            ['titre' => 'Audit du SI existant', 'phase_id' => $phase1->id, 'statut_id' => $statuts['Terminé'] ?? null, 'priorite_id' => $priorites['Haute'] ?? null, 'responsable_id' => $user1->id, 'avancement' => 100, 'date_debut' => now()->subMonths(3), 'date_fin' => now()->subMonths(2)],
            ['titre' => 'Rédaction du cahier des charges', 'phase_id' => $phase1->id, 'statut_id' => $statuts['Terminé'] ?? null, 'priorite_id' => $priorites['Haute'] ?? null, 'responsable_id' => $user2->id, 'avancement' => 100, 'date_debut' => now()->subMonths(2), 'date_fin' => now()->subMonth()],
            ['titre' => 'Architecture cloud cible', 'phase_id' => $phase2->id, 'statut_id' => $statuts['En cours'] ?? null, 'priorite_id' => $priorites['Haute'] ?? null, 'responsable_id' => $user2->id, 'avancement' => 70, 'date_debut' => now()->subMonth(), 'date_fin' => now()->addWeeks(2)],
            ['titre' => 'Maquettes des interfaces', 'phase_id' => $phase2->id, 'statut_id' => $statuts['En cours'] ?? null, 'priorite_id' => $priorites['Normal'] ?? null, 'responsable_id' => $user3->id, 'avancement' => 40, 'date_debut' => now()->subWeeks(2), 'date_fin' => now()->addWeeks(3)],
            ['titre' => 'Choix du prestataire cloud', 'phase_id' => $phase2->id, 'statut_id' => $statuts['En cours'] ?? null, 'priorite_id' => $priorites['Urgente'] ?? null, 'responsable_id' => $user1->id, 'avancement' => 20, 'date_debut' => now()->subWeeks(3), 'date_fin' => now()->subDays(5)], // EN RETARD
            ['titre' => 'Développement module RH', 'phase_id' => $phase3->id, 'statut_id' => $statuts['Non démarré'] ?? null, 'priorite_id' => $priorites['Normal'] ?? null, 'responsable_id' => $user3->id, 'avancement' => 0, 'date_debut' => now()->addMonth(), 'date_fin' => now()->addMonths(4)],
            ['titre' => 'Développement module Finance', 'phase_id' => $phase3->id, 'statut_id' => $statuts['Non démarré'] ?? null, 'priorite_id' => $priorites['Haute'] ?? null, 'responsable_id' => $user2->id, 'avancement' => 0, 'date_debut' => now()->addMonth(), 'date_fin' => now()->addMonths(5)],
            ['titre' => 'Migration des données', 'phase_id' => $phase3->id, 'statut_id' => $statuts['En cours'] ?? null, 'priorite_id' => $priorites['Urgente'] ?? null, 'responsable_id' => $user1->id, 'avancement' => 15, 'date_debut' => now()->subWeeks(1), 'date_fin' => now()->subDays(2)], // EN RETARD + URGENTE
            ['titre' => 'Tests d\'intégration', 'phase_id' => $phase4->id, 'statut_id' => $statuts['Non démarré'] ?? null, 'priorite_id' => $priorites['Normal'] ?? null, 'responsable_id' => $user3->id, 'avancement' => 0, 'date_debut' => now()->addMonths(6), 'date_fin' => now()->addMonths(7)],
            ['titre' => 'Formation utilisateurs', 'phase_id' => $phase4->id, 'statut_id' => $statuts['Non démarré'] ?? null, 'priorite_id' => $priorites['Normal'] ?? null, 'responsable_id' => $user1->id, 'avancement' => 0, 'date_debut' => now()->addMonths(7), 'date_fin' => now()->addMonths(8)],
        ];

        foreach ($taches as $td) {
            Tache::updateOrCreate(
                ['titre' => $td['titre'], 'projet_id' => $p1->id],
                array_merge($td, ['projet_id' => $p1->id, 'created_by' => $user1->id])
            );
        }

        // Jalons
        $jalons = [
            ['titre' => 'Validation du cahier des charges', 'phase_id' => $phase1->id, 'date_prevue' => now()->subMonth(), 'date_reelle' => now()->subMonth()->addDays(2), 'statut' => 'atteint'],
            ['titre' => 'Revue d\'architecture', 'phase_id' => $phase2->id, 'date_prevue' => now()->addWeeks(2), 'statut' => 'prevu'],
            ['titre' => 'Go/No-Go développement', 'phase_id' => $phase2->id, 'date_prevue' => now()->addMonth(), 'statut' => 'prevu'],
            ['titre' => 'Livraison module RH', 'phase_id' => $phase3->id, 'date_prevue' => now()->addMonths(4), 'statut' => 'prevu'],
            ['titre' => 'Livraison module Finance', 'phase_id' => $phase3->id, 'date_prevue' => now()->addMonths(5), 'statut' => 'prevu'],
            ['titre' => 'Mise en production', 'phase_id' => $phase4->id, 'date_prevue' => now()->addMonths(8), 'statut' => 'prevu'],
            ['titre' => 'Réception définitive', 'phase_id' => $phase4->id, 'date_prevue' => now()->subDays(3), 'statut' => 'prevu'], // EN RETARD
        ];
        foreach ($jalons as $jd) {
            Jalon::updateOrCreate(
                ['titre' => $jd['titre'], 'projet_id' => $p1->id],
                array_merge($jd, ['projet_id' => $p1->id, 'created_by' => $user1->id])
            );
        }

        // Coûts
        $couts = [
            ['libelle' => 'Licences cloud Azure', 'categorie' => 'service', 'montant_estime' => 15000000, 'montant_reel' => 12500000, 'date_cout' => now()->subMonth()],
            ['libelle' => 'Prestation audit externe', 'categorie' => 'service', 'montant_estime' => 5000000, 'montant_reel' => 5800000, 'date_cout' => now()->subMonths(2)],
            ['libelle' => 'Développement sur mesure', 'categorie' => 'main_oeuvre', 'montant_estime' => 25000000, 'montant_reel' => 8000000, 'date_cout' => now()],
            ['libelle' => 'Formation équipe interne', 'categorie' => 'formation', 'montant_estime' => 3000000, 'montant_reel' => 1200000, 'date_cout' => now()->subWeeks(2)],
            ['libelle' => 'Matériel serveurs backup', 'categorie' => 'materiel', 'montant_estime' => 8000000, 'montant_reel' => 9200000, 'date_cout' => now()->subWeeks(3)],
        ];
        foreach ($couts as $cd) {
            ProjetCout::updateOrCreate(
                ['libelle' => $cd['libelle'], 'projet_id' => $p1->id],
                array_merge($cd, ['projet_id' => $p1->id, 'created_by' => $user1->id])
            );
        }

        // EVM
        ProjetEvm::updateOrCreate(['projet_id' => $p1->id, 'date_mesure' => now()->subMonth()], [
            'bac' => 75000000, 'pv' => 20000000, 'ev' => 18000000, 'ac' => 22000000,
            'sv' => -2000000, 'cv' => -4000000, 'spi' => 0.90, 'cpi' => 0.82,
            'etc' => 69512195, 'eac' => 91512195, 'commentaire' => 'Dépassement coût identifié sur le poste infrastructure.',
            'created_by' => $user1->id,
        ]);
        ProjetEvm::updateOrCreate(['projet_id' => $p1->id, 'date_mesure' => now()], [
            'bac' => 75000000, 'pv' => 30000000, 'ev' => 26250000, 'ac' => 36700000,
            'sv' => -3750000, 'cv' => -10450000, 'spi' => 0.875, 'cpi' => 0.715,
            'etc' => 68181818, 'eac' => 104881818, 'commentaire' => 'SPI et CPI en baisse. Action corrective requise sur les coûts.',
            'created_by' => $user1->id,
        ]);

        // Risques
        $risques = [
            ['titre' => 'Dépassement budgétaire infrastructure', 'description' => 'Les coûts cloud dépassent les estimations initiales de 20%.', 'categorie' => 'Financier', 'probabilite' => 4, 'impact' => 5, 'type_risque' => 'menace', 'strategie' => 'attenuer', 'plan_reponse' => 'Négocier les tarifs cloud. Envisager un plan B on-premise partiel.', 'statut' => 'identifie'],
            ['titre' => 'Résistance au changement des utilisateurs', 'description' => 'Les utilisateurs métier risquent de ne pas adopter le nouveau SI.', 'categorie' => 'Organisationnel', 'probabilite' => 3, 'impact' => 4, 'type_risque' => 'menace', 'strategie' => 'attenuer', 'plan_reponse' => 'Plan de communication et formation renforcé.', 'statut' => 'analyse'],
            ['titre' => 'Perte de données durant migration', 'description' => 'La migration des données existantes peut entraîner des pertes.', 'categorie' => 'Technique', 'probabilite' => 2, 'impact' => 5, 'type_risque' => 'menace', 'strategie' => 'eviter', 'plan_reponse' => 'Double sauvegarde + migration par lots avec validation.', 'statut' => 'identifie'],
            ['titre' => 'Indisponibilité du prestataire cloud', 'description' => 'Le fournisseur cloud pourrait avoir des problèmes de disponibilité.', 'categorie' => 'Externe', 'probabilite' => 2, 'impact' => 4, 'type_risque' => 'menace', 'strategie' => 'transferer', 'plan_reponse' => 'Clause SLA dans le contrat + prestataire backup identifié.', 'statut' => 'analyse'],
            ['titre' => 'Démission du développeur senior', 'description' => 'Le développeur clé pourrait quitter le projet.', 'categorie' => 'RH', 'probabilite' => 3, 'impact' => 5, 'type_risque' => 'menace', 'strategie' => 'attenuer', 'plan_reponse' => 'Documentation technique et formation croisée.', 'statut' => 'identifie'],
            ['titre' => 'Réduction des délais grâce à l\'IA', 'description' => 'L\'IA pourrait accélérer le développement de 20%.', 'categorie' => 'Technique', 'probabilite' => 3, 'impact' => 3, 'type_risque' => 'opportunite', 'strategie' => 'exploiter', 'plan_reponse' => 'Intégrer des outils IA dans le développement.', 'statut' => 'identifie'],
        ];
        foreach ($risques as $rd) {
            ProjetRisque::updateOrCreate(
                ['titre' => $rd['titre'], 'projet_id' => $p1->id],
                array_merge($rd, ['projet_id' => $p1->id, 'responsable_id' => $user1->id, 'date_identification' => now()->subWeeks(rand(1, 8)), 'created_by' => $user1->id])
            );
        }

        // Problèmes
        $problemes = [
            ['titre' => 'Serveur de test indisponible depuis 3 jours', 'description' => 'L\'environnement de test est en panne. Impact direct sur la phase de conception.', 'statut' => 'ouvert', 'impact' => 'Blocage des tests de validation architecture.', 'priorite_id' => $priorites['Urgente'] ?? null],
            ['titre' => 'API fournisseur non documentée', 'description' => 'Le fournisseur cloud n\'a pas fourni la documentation API complète.', 'statut' => 'en_cours', 'impact' => 'Retard de 5 jours sur le développement.', 'priorite_id' => $priorites['Haute'] ?? null],
            ['titre' => 'Conflit de versions sur les dépendances', 'description' => 'Incompatibilité entre les versions de Laravel et le package LDAP.', 'statut' => 'resolu', 'impact' => 'Résolu par mise à jour du package.', 'resolution' => 'Upgrade du package vers v3.2.', 'priorite_id' => $priorites['Normal'] ?? null, 'date_resolution' => now()->subDays(5)],
        ];
        foreach ($problemes as $pd) {
            ProjetProbleme::updateOrCreate(
                ['titre' => $pd['titre'], 'projet_id' => $p1->id],
                array_merge($pd, ['projet_id' => $p1->id, 'responsable_id' => $user2->id, 'date_identification' => now()->subWeeks(rand(1, 4)), 'created_by' => $user1->id])
            );
        }

        // Changements
        $changements = [
            ['titre' => 'Ajout module gestion documentaire', 'description' => 'Le sponsor demande l\'ajout d\'un module GED non prévu initialement.', 'type' => 'perimetre', 'justification' => 'Besoin exprimé par la direction juridique.', 'impact_cout' => 8000000, 'impact_delai_jours' => 30, 'statut' => 'soumis', 'demandeur_id' => $user2->id],
            ['titre' => 'Report de la mise en production de 2 semaines', 'description' => 'Tests insuffisants nécessitent un délai supplémentaire.', 'type' => 'delai', 'justification' => 'Qualité insuffisante des livrables actuels.', 'impact_cout' => 2500000, 'impact_delai_jours' => 14, 'statut' => 'soumis', 'demandeur_id' => $user1->id],
            ['titre' => 'Remplacement du prestataire réseau', 'description' => 'Le prestataire actuel ne respecte pas les SLA.', 'type' => 'autre', 'justification' => 'Performances réseau insuffisantes.', 'impact_cout' => 3000000, 'impact_delai_jours' => 7, 'statut' => 'approuve', 'demandeur_id' => $user3->id, 'approuve_par' => $user1->id, 'date_decision' => now()->subDays(5), 'decision_commentaire' => 'Approuvé. Nouveau prestataire identifié.'],
        ];
        foreach ($changements as $cd) {
            ProjetChangement::updateOrCreate(
                ['titre' => $cd['titre'], 'projet_id' => $p1->id],
                array_merge($cd, ['projet_id' => $p1->id, 'date_soumission' => now()->subWeeks(rand(1, 3)), 'created_by' => $user1->id])
            );
        }

        // Ressources — résoudre les rôles paramétrables par leur code
        $rolesByCode = \App\Models\Intranet\RoleProjet::pluck('id', 'code');
        $ressourcesData = [
            ['user_id' => $user1->id, 'role_projet_id' => $rolesByCode['chef']   ?? null, 'heures_allouees' => 800,  'heures_reelles' => 280, 'taux_journalier' => 150000],
            ['user_id' => $user2->id, 'role_projet_id' => $rolesByCode['expert'] ?? null, 'heures_allouees' => 600,  'heures_reelles' => 220, 'taux_journalier' => 180000],
            ['user_id' => $user3->id, 'role_projet_id' => $rolesByCode['membre'] ?? null, 'heures_allouees' => 1200, 'heures_reelles' => 150, 'taux_journalier' => 120000],
        ];
        foreach ($ressourcesData as $rd) {
            ProjetRessource::updateOrCreate(
                ['projet_id' => $p1->id, 'user_id' => $rd['user_id']],
                array_merge($rd, ['projet_id' => $p1->id, 'est_actif' => true, 'date_debut' => now()->subMonths(3), 'date_fin' => now()->addMonths(9)])
            );
        }

        // Livrables
        $livrables = [
            ['titre' => 'Document d\'architecture technique', 'phase_id' => $phase2->id, 'statut' => 'en_cours', 'date_prevue' => now()->addWeeks(2), 'criteres_acceptation' => 'Validé par le comité technique. Couvre tous les modules.'],
            ['titre' => 'Rapport d\'audit SI existant', 'phase_id' => $phase1->id, 'statut' => 'accepte', 'date_prevue' => now()->subMonths(2), 'date_livraison' => now()->subMonths(2)->addDays(3)],
            ['titre' => 'Module RH opérationnel', 'phase_id' => $phase3->id, 'statut' => 'planifie', 'date_prevue' => now()->addMonths(4)],
            ['titre' => 'Plan de migration des données', 'phase_id' => $phase2->id, 'statut' => 'en_cours', 'date_prevue' => now()->subDays(5)], // EN RETARD
        ];
        foreach ($livrables as $ld) {
            ProjetLivrable::updateOrCreate(
                ['titre' => $ld['titre'], 'projet_id' => $p1->id],
                array_merge($ld, ['projet_id' => $p1->id, 'responsable_id' => $user1->id, 'created_by' => $user1->id])
            );
        }

        // Parties prenantes
        $pp = [
            ['user_id' => $user1->id, 'role_projet' => 'Chef de projet', 'categorie' => 'interne', 'interet' => 5, 'influence' => 5, 'engagement_actuel' => 'champion', 'engagement_desire' => 'champion', 'attentes' => 'Livraison dans les délais et le budget.'],
            ['user_id' => $user2->id, 'role_projet' => 'Sponsor', 'categorie' => 'interne', 'interet' => 4, 'influence' => 5, 'engagement_actuel' => 'favorable', 'engagement_desire' => 'champion', 'attentes' => 'ROI visible sous 12 mois.'],
            ['nom_externe' => 'Direction Générale', 'organisation_externe' => 'ANPI', 'role_projet' => 'Commanditaire', 'categorie' => 'interne', 'interet' => 5, 'influence' => 5, 'engagement_actuel' => 'neutre', 'engagement_desire' => 'favorable'],
            ['nom_externe' => 'CloudTech SARL', 'organisation_externe' => 'Prestataire Cloud', 'role_projet' => 'Fournisseur infrastructure', 'categorie' => 'externe', 'interet' => 3, 'influence' => 3, 'engagement_actuel' => 'favorable', 'engagement_desire' => 'favorable'],
        ];
        foreach ($pp as $ppd) {
            ProjetPartiePrenante::updateOrCreate(
                ['projet_id' => $p1->id, 'role_projet' => $ppd['role_projet']],
                array_merge($ppd, ['projet_id' => $p1->id, 'created_by' => $user1->id])
            );
        }

        // Leçons apprises
        $lecons = [
            ['titre' => 'L\'audit initial a été sous-estimé en durée', 'type' => 'echec', 'categorie' => 'Planification', 'description' => 'L\'audit du SI existant a pris 3 semaines au lieu de 2.', 'impact' => 'Décalage de 1 semaine sur la phase suivante.', 'recommandation' => 'Prévoir 50% de marge sur les audits techniques.'],
            ['titre' => 'La documentation automatisée a accéléré la conception', 'type' => 'succes', 'categorie' => 'Technique', 'description' => 'L\'utilisation d\'outils de génération automatique de documentation a réduit le temps de rédaction.', 'impact' => 'Gain de 2 semaines.', 'recommandation' => 'Systématiser l\'utilisation de ces outils.'],
            ['titre' => 'Impliquer les utilisateurs plus tôt', 'type' => 'amelioration', 'categorie' => 'Organisationnel', 'description' => 'Les retours utilisateurs arrivent tard dans le cycle.', 'recommandation' => 'Organiser des démos bimensuelles dès la phase de conception.'],
        ];
        foreach ($lecons as $ld) {
            ProjetLecon::updateOrCreate(
                ['titre' => $ld['titre'], 'projet_id' => $p1->id],
                array_merge($ld, ['projet_id' => $p1->id, 'statut' => 'valide', 'created_by' => $user1->id])
            );
        }

        // Feuilles de temps
        for ($i = 0; $i < 15; $i++) {
            $tache = Tache::where('projet_id', $p1->id)->inRandomOrder()->first();
            FeuilleTemps::updateOrCreate(
                ['user_id' => $users->random()->id, 'projet_id' => $p1->id, 'date' => now()->subDays($i)],
                [
                    'tache_id'    => $tache?->id,
                    'heures'      => rand(2, 8),
                    'description' => collect(['Développement', 'Analyse', 'Tests', 'Réunion', 'Documentation', 'Conception', 'Revue de code'])->random(),
                    'statut'      => $i > 5 ? 'approuve' : 'soumis',
                    'approuve_par'=> $i > 5 ? $user1->id : null,
                    'approuve_le' => $i > 5 ? now()->subDays($i - 1) : null,
                ]
            );
        }

        // ══════════════════════════════════════════════════
        // PROJET 2 : Déploiement CRM (plus simple)
        // ══════════════════════════════════════════════════
        $p2 = Projet::updateOrCreate(['code_projet' => 'PRJ-002'], [
            'nom'             => 'Déploiement CRM Commercial',
            'code_projet'     => 'PRJ-002',
            'categorie'       => 'Commercial',
            'description'     => '<p>Mise en place du CRM pour l\'équipe commerciale : contacts, opportunités, pipeline de vente.</p>',
            'budget_approuve' => 15000000,
            'devise'          => 'XAF',
            'priorite_id'     => $priorites['Normal'] ?? null,
            'statut_id'       => $statuts['En cours'] ?? null,
            'chef_projet_id'  => $user2->id,
            'created_by'      => $user1->id,
            'date_debut'      => now()->subMonth(),
            'date_fin'        => now()->addMonths(3),
            'avancement'      => 20,
        ]);

        $p2->membres()->syncWithoutDetaching([$user1->id, $user2->id]);

        $p2Phase = ProjetPhase::updateOrCreate(['projet_id' => $p2->id, 'code_wbs' => '1.0'], [
            'nom' => 'Configuration CRM', 'code_wbs' => '1.0', 'ordre' => 1,
            'avancement' => 40, 'couleur' => '#DB2777', 'created_by' => $user1->id,
        ]);

        Tache::updateOrCreate(['titre' => 'Paramétrage des étapes pipeline', 'projet_id' => $p2->id], [
            'phase_id' => $p2Phase->id, 'statut_id' => $statuts['Terminé'] ?? null, 'priorite_id' => $priorites['Haute'] ?? null,
            'responsable_id' => $user2->id, 'avancement' => 100, 'date_debut' => now()->subMonth(), 'date_fin' => now()->subWeeks(2), 'created_by' => $user1->id,
        ]);
        Tache::updateOrCreate(['titre' => 'Import des contacts existants', 'projet_id' => $p2->id], [
            'phase_id' => $p2Phase->id, 'statut_id' => $statuts['En cours'] ?? null, 'priorite_id' => $priorites['Normal'] ?? null,
            'responsable_id' => $user1->id, 'avancement' => 30, 'date_debut' => now()->subWeeks(2), 'date_fin' => now()->addWeek(), 'created_by' => $user1->id,
        ]);
        Tache::updateOrCreate(['titre' => 'Formation équipe commerciale', 'projet_id' => $p2->id], [
            'phase_id' => $p2Phase->id, 'statut_id' => $statuts['Non démarré'] ?? null, 'priorite_id' => $priorites['Normal'] ?? null,
            'responsable_id' => $user2->id, 'avancement' => 0, 'date_debut' => now()->addWeeks(2), 'date_fin' => now()->addMonths(2), 'created_by' => $user1->id,
        ]);

        ProjetRisque::updateOrCreate(['titre' => 'Adoption faible par les commerciaux', 'projet_id' => $p2->id], [
            'description' => 'Les commerciaux pourraient ne pas utiliser le CRM au quotidien.',
            'categorie' => 'Organisationnel', 'probabilite' => 4, 'impact' => 4, 'type_risque' => 'menace',
            'strategie' => 'attenuer', 'plan_reponse' => 'Accompagnement individualisé et gamification.',
            'statut' => 'identifie', 'responsable_id' => $user2->id, 'date_identification' => now()->subWeeks(2), 'created_by' => $user1->id,
        ]);

        // ══════════════════════════════════════════════════
        // PROJET 3 : Terminé
        // ══════════════════════════════════════════════════
        Projet::updateOrCreate(['code_projet' => 'PRJ-003'], [
            'nom'             => 'Mise en conformité RGPD',
            'code_projet'     => 'PRJ-003',
            'categorie'       => 'Juridique',
            'description'     => '<p>Audit et mise en conformité RGPD de l\'ensemble des traitements de données personnelles.</p>',
            'budget_approuve' => 5000000,
            'devise'          => 'XAF',
            'priorite_id'     => $priorites['Haute'] ?? null,
            'statut_id'       => $statuts['Terminé'] ?? null,
            'chef_projet_id'  => $user3->id,
            'created_by'      => $user1->id,
            'date_debut'      => now()->subMonths(6),
            'date_fin'        => now()->subMonth(),
            'avancement'      => 100,
        ]);
    }
}
