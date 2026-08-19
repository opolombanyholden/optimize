<?php

namespace Database\Seeders;

use App\Models\Intranet\Groupe;
use App\Models\Intranet\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AnnuaireSeeder extends Seeder
{
    public function run(): void
    {
        // Compléter les users existants avec des champs annuaire
        $admin = User::where('email', 'admin@optimize.local')->first();
        $test  = User::where('email', 'test@optimize.local')->first();

        if ($admin) {
            $admin->update([
                'matricule'      => 'ADM-001',
                'poste'          => 'Directeur Système d\'Information',
                'date_embauche'  => '2024-01-15',
                'bureau'         => 'B-201',
                'bio'            => 'En charge de la stratégie SI et de la transformation digitale.',
            ]);
        }
        if ($test) {
            $test->update([
                'matricule'      => 'USR-001',
                'poste'          => 'Développeur Full-Stack',
                'date_embauche'  => '2025-03-01',
                'bureau'         => 'B-104',
            ]);
        }

        // Créer des collaborateurs de démo
        $collaborateurs = [
            ['name' => 'Mensah',     'prenoms' => 'Aminata',    'email' => 'aminata.mensah@optimize.local',    'matricule' => 'RH-002',  'poste' => 'Responsable RH',           'date_embauche' => '2023-06-10', 'bureau' => 'A-301', 'contact' => '+241 06 11 22 33'],
            ['name' => 'Obame',      'prenoms' => 'Jean-Pierre','email' => 'jp.obame@optimize.local',          'matricule' => 'FIN-002', 'poste' => 'Comptable Senior',         'date_embauche' => '2022-09-20', 'bureau' => 'A-205', 'contact' => '+241 06 22 33 44'],
            ['name' => 'Mba',        'prenoms' => 'Sylvie',     'email' => 'sylvie.mba@optimize.local',         'matricule' => 'COM-002', 'poste' => 'Chargée de Communication', 'date_embauche' => '2024-02-15', 'bureau' => 'C-110', 'contact' => '+241 06 33 44 55'],
            ['name' => 'Nguema',     'prenoms' => 'Patrick',    'email' => 'patrick.nguema@optimize.local',    'matricule' => 'DSI-003', 'poste' => 'Architecte Solutions',     'date_embauche' => '2023-11-01', 'bureau' => 'B-203', 'contact' => '+241 06 44 55 66'],
            ['name' => 'Bouanga',    'prenoms' => 'Linda',      'email' => 'linda.bouanga@optimize.local',     'matricule' => 'COM-001', 'poste' => 'Directrice Marketing',     'date_embauche' => '2022-04-05', 'bureau' => 'C-301', 'contact' => '+241 06 55 66 77'],
            ['name' => 'Ondo',       'prenoms' => 'Claude',     'email' => 'claude.ondo@optimize.local',       'matricule' => 'APP-001', 'poste' => 'Acheteur',                 'date_embauche' => '2024-08-10', 'bureau' => 'A-150', 'contact' => '+241 06 66 77 88'],
        ];

        foreach ($collaborateurs as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                array_merge($c, [
                    'password' => Hash::make('password'),
                    'statut'   => 1,
                ])
            );
            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }
        }

        // Services
        $services = [
            ['nom' => 'Direction Générale',           'code' => 'DG',   'couleur' => '#0F172A', 'icone' => 'fa-crown',         'localisation' => 'Étage 3', 'description' => 'Direction et stratégie globale de l\'entreprise.'],
            ['nom' => 'Système d\'Information',        'code' => 'DSI',  'couleur' => '#0D9488', 'icone' => 'fa-server',        'localisation' => 'Bâtiment B', 'description' => 'Gestion du SI, infrastructure, développement et innovation.'],
            ['nom' => 'Ressources Humaines',          'code' => 'RH',   'couleur' => '#16A34A', 'icone' => 'fa-users',         'localisation' => 'Étage 3', 'description' => 'Recrutement, paie, formations, carrière.'],
            ['nom' => 'Finance & Comptabilité',       'code' => 'FIN',  'couleur' => '#D97706', 'icone' => 'fa-coins',         'localisation' => 'Étage 2', 'description' => 'Comptabilité, trésorerie, contrôle de gestion.'],
            ['nom' => 'Communication & Marketing',    'code' => 'COM',  'couleur' => '#7C3AED', 'icone' => 'fa-bullhorn',      'localisation' => 'Étage 3', 'description' => 'Communication interne et externe, marketing.'],
            ['nom' => 'Achats & Approvisionnement',   'code' => 'APP',  'couleur' => '#0891B2', 'icone' => 'fa-cart-flatbed',  'localisation' => 'RDC',     'description' => 'Achats, fournisseurs, supply chain.'],
        ];

        foreach ($services as $sd) {
            Service::firstOrCreate(['code' => $sd['code']], array_merge($sd, ['est_actif' => true]));
        }

        // Affecter les chefs de service
        $assignations = [
            'DG'  => 'admin@optimize.local',
            'DSI' => 'admin@optimize.local',  // chef DSI par défaut
            'RH'  => 'aminata.mensah@optimize.local',
            'FIN' => 'jp.obame@optimize.local',
            'COM' => 'linda.bouanga@optimize.local',
            'APP' => 'claude.ondo@optimize.local',
        ];

        foreach ($assignations as $code => $email) {
            $service = Service::where('code', $code)->first();
            $chef    = User::where('email', $email)->first();
            if ($service && $chef) {
                $service->update(['chef_du_service_id' => $chef->id]);
            }
        }

        // Affecter les membres aux services (avec poste + date arrivée)
        $membres = [
            'DSI' => [
                ['email' => 'admin@optimize.local',      'poste' => 'Directeur SI',         'principal' => true],
                ['email' => 'patrick.nguema@optimize.local', 'poste' => 'Architecte Solutions', 'principal' => true],
                ['email' => 'test@optimize.local',       'poste' => 'Développeur Full-Stack','principal' => true],
            ],
            'RH' => [
                ['email' => 'aminata.mensah@optimize.local', 'poste' => 'Responsable RH',  'principal' => true],
            ],
            'FIN' => [
                ['email' => 'jp.obame@optimize.local',  'poste' => 'Comptable Senior', 'principal' => true],
            ],
            'COM' => [
                ['email' => 'linda.bouanga@optimize.local', 'poste' => 'Directrice Marketing',     'principal' => true],
                ['email' => 'sylvie.mba@optimize.local',    'poste' => 'Chargée de Communication', 'principal' => true],
            ],
            'APP' => [
                ['email' => 'claude.ondo@optimize.local', 'poste' => 'Acheteur', 'principal' => true],
            ],
        ];

        foreach ($membres as $code => $list) {
            $service = Service::where('code', $code)->first();
            if (! $service) continue;
            foreach ($list as $m) {
                $user = User::where('email', $m['email'])->first();
                if (! $user) continue;
                $service->membres()->syncWithoutDetaching([
                    $user->id => [
                        'poste'         => $m['poste'],
                        'est_principal' => $m['principal'] ?? false,
                        'date_arrivee'  => $user->date_embauche ?? now()->toDateString(),
                    ],
                ]);
            }
        }

        // Équipes transverses (Groupes)
        $equipes = [
            [
                'nom' => 'Comité de Direction',
                'description' => 'Réunit les directeurs et chefs de service pour piloter la stratégie.',
                'couleur' => '#0F172A', 'icone' => 'fa-crown',
                'membres' => ['admin@optimize.local', 'aminata.mensah@optimize.local', 'linda.bouanga@optimize.local', 'jp.obame@optimize.local'],
            ],
            [
                'nom' => 'Taskforce Migration ERP',
                'description' => 'Équipe transverse pour le déploiement et l\'adoption de l\'ERP OptimiZe.',
                'couleur' => '#0D9488', 'icone' => 'fa-rocket',
                'membres' => ['admin@optimize.local', 'patrick.nguema@optimize.local', 'test@optimize.local', 'jp.obame@optimize.local'],
            ],
            [
                'nom' => 'Communauté DevOps',
                'description' => 'Communauté de pratique sur les sujets DevOps, CI/CD, infrastructure.',
                'couleur' => '#7C3AED', 'icone' => 'fa-infinity',
                'membres' => ['patrick.nguema@optimize.local', 'test@optimize.local'],
            ],
        ];

        foreach ($equipes as $ed) {
            $equipe = Groupe::firstOrCreate(
                ['nom' => $ed['nom']],
                [
                    'description' => $ed['description'],
                    'couleur'     => $ed['couleur'],
                    'icone'       => $ed['icone'],
                    'created_by'  => $admin?->id ?? 1,
                ]
            );
            foreach ($ed['membres'] as $email) {
                $u = User::where('email', $email)->first();
                if ($u && ! $equipe->membres->contains($u->id)) {
                    $equipe->membres()->attach($u->id, ['role' => $email === $admin?->email ? 'animateur' : 'membre']);
                }
            }
        }

        $this->command->info('Annuaire seeded: ' . User::count() . ' users, ' . Service::count() . ' services, ' . Groupe::count() . ' équipes.');
    }
}
