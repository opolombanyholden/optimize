<?php
namespace Database\Seeders;

use App\Models\Intranet\Statut;
use App\Models\Intranet\Priorite;
use App\Models\Intranet\TypeEvenement;
use App\Models\Intranet\Annonce;
use App\Models\Intranet\News;
use App\Models\Intranet\Evenement;
use App\Models\Intranet\Projet;
use App\Models\Intranet\Tache;
use App\Models\User;
use Illuminate\Database\Seeder;

class IntranetSeeder extends Seeder
{
    public function run(): void
    {
        // Statuts
        $statuts = [
            ['libelle' => 'Non démarré', 'couleur' => '#64748b'],
            ['libelle' => 'En cours',    'couleur' => '#4F46E5'],
            ['libelle' => 'En pause',    'couleur' => '#D97706'],
            ['libelle' => 'Terminé',     'couleur' => '#059669'],
            ['libelle' => 'Annulé',      'couleur' => '#DC2626'],
        ];
        foreach ($statuts as $s) Statut::firstOrCreate(['libelle' => $s['libelle']], $s);

        // Priorités
        $priorites = [
            ['libelle' => 'Basse',   'couleur' => '#94A3B8'],
            ['libelle' => 'Normal',  'couleur' => '#4F46E5'],
            ['libelle' => 'Haute',   'couleur' => '#D97706'],
            ['libelle' => 'Urgente', 'couleur' => '#DC2626'],
        ];
        foreach ($priorites as $p) Priorite::firstOrCreate(['libelle' => $p['libelle']], $p);

        // Types événements
        $types = [
            ['nom' => 'Rendez-vous client',  'code' => 'rdv',       'couleur' => '#DC2626'],
            ['nom' => 'Réunion interne',      'code' => 'reunion',   'couleur' => '#4F46E5'],
            ['nom' => 'Formation',            'code' => 'formation', 'couleur' => '#D97706'],
            ['nom' => 'Conférence',           'code' => 'conf',      'couleur' => '#059669'],
            ['nom' => 'Atelier',              'code' => 'atelier',   'couleur' => '#0891B2'],
        ];
        foreach ($types as $t) TypeEvenement::firstOrCreate(['code' => $t['code']], $t);

        $admin = User::first();
        if (!$admin) return;

        // Annonces de démo
        $annonces = [
            ['title' => 'Bienvenue sur le portail OptimiZe Intranet', 'content' => 'L\'intranet OptimiZe est désormais opérationnel. Retrouvez toutes les informations de l\'organisation en un seul endroit.', 'is_public' => true,  'is_urgent' => false],
            ['title' => 'Réunion de direction — 15 Avril 2026',       'content' => 'La réunion mensuelle de direction aura lieu le 15 avril 2026 à 9h00 en salle de conférence.',                       'is_public' => true,  'is_urgent' => false],
            ['title' => 'Mise à jour de la politique de congés',      'content' => 'Veuillez prendre connaissance de la nouvelle politique de gestion des congés en vigueur à partir du 1er mai.',      'is_public' => true,  'is_urgent' => true],
        ];
        foreach ($annonces as $a) {
            Annonce::firstOrCreate(['title' => $a['title']], array_merge($a, ['created_by' => $admin->id]));
        }

        // News de démo
        $news = [
            ['title' => 'Lancement du nouveau module Intranet',       'content' => 'OptimiZe intègre désormais un module intranet complet pour centraliser les communications et la gestion documentaire.'],
            ['title' => 'Formation aux outils collaboratifs',         'content' => 'Une session de formation sur les nouveaux outils collaboratifs est prévue pour l\'ensemble des équipes.'],
        ];
        foreach ($news as $n) {
            \App\Models\Intranet\News::firstOrCreate(['title' => $n['title']], array_merge($n, ['created_by' => $admin->id]));
        }

        // Événements de démo
        $typeReunion = TypeEvenement::where('code','reunion')->first();
        $typeFormation = TypeEvenement::where('code','formation')->first();
        if ($typeReunion) {
            Evenement::firstOrCreate(['titre' => 'Réunion mensuelle — Avril'], [
                'date_debut' => now()->addDays(7)->setTime(9,0),
                'date_fin'   => now()->addDays(7)->setTime(11,0),
                'lieu'       => 'Salle de conférence A',
                'statut'     => 'prevu', 'is_public' => true,
                'type_evenement_id' => $typeReunion->id, 'created_by' => $admin->id,
            ]);
        }
        if ($typeFormation) {
            Evenement::firstOrCreate(['titre' => 'Formation : Prise en main OptimiZe'], [
                'date_debut' => now()->addDays(14)->setTime(14,0),
                'date_fin'   => now()->addDays(14)->setTime(17,0),
                'lieu'       => 'Salle informatique',
                'statut'     => 'prevu', 'is_public' => true,
                'type_evenement_id' => $typeFormation->id, 'created_by' => $admin->id,
            ]);
        }

        // Projets de démo
        $statEnCours = Statut::where('libelle','En cours')->first();
        $statNonDemarre = Statut::where('libelle','Non démarré')->first();
        $priHaute = Priorite::where('libelle','Haute')->first();
        $priNormal = Priorite::where('libelle','Normal')->first();

        if ($statEnCours && $priHaute) {
            $projet = Projet::firstOrCreate(['nom' => 'Déploiement OptimiZe ERP'], [
                'description' => 'Mise en production complète de la solution ERP OptimiZe.',
                'priorite_id' => $priHaute->id, 'statut_id' => $statEnCours->id,
                'created_by'  => $admin->id,
                'date_debut'  => now()->subMonth(), 'date_fin' => now()->addMonths(2),
            ]);
            // Tâches rattachées
            if ($statEnCours && $priNormal) {
                Tache::firstOrCreate(['titre' => 'Configuration du module Finance'], [
                    'projet_id' => $projet->id, 'priorite_id' => $priNormal->id,
                    'statut_id' => $statEnCours->id, 'created_by' => $admin->id,
                    'date_fin'  => now()->addDays(10),
                ]);
                Tache::firstOrCreate(['titre' => 'Import des données employés'], [
                    'projet_id' => $projet->id, 'priorite_id' => $priHaute->id,
                    'statut_id' => $statNonDemarre?->id, 'created_by' => $admin->id,
                    'date_fin'  => now()->addDays(5),
                ]);
            }
        }
    }
}
