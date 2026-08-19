<?php

namespace Database\Seeders;

use App\Models\Absence;
use App\Models\CommandeFournisseur;
use App\Models\Compte;
use App\Models\Dysfonctionnement;
use App\Models\Employee;
use App\Models\Exercice;
use App\Models\Fournisseur;
use App\Models\Immobilisation;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Database\Seeder;

class ErpDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@optimize.local')->first();
        if (! $admin) {
            $this->command->warn('Lancer DatabaseSeeder d\'abord (admin requis).');
            return;
        }

        $this->seedFinance($admin);
        $this->seedRH($admin);
        $this->seedAppro($admin);
        $this->seedMG($admin);

        $this->command->info('ERP demo seeded.');
    }

    /* ─────────────────────────────────────────────────── */
    /* FINANCE                                              */
    /* ─────────────────────────────────────────────────── */
    private function seedFinance(User $admin): void
    {
        // Exercices budgétaires
        Exercice::firstOrCreate(
            ['exercice' => '2026'],
            [
                'libelle'              => 'Exercice 2026',
                'datedebut'            => '2026-01-01',
                'datefin'              => '2026-12-31',
                'budgetglobalinitial'  => 1_500_000_000,
                'dotationglobale'      => 1_200_000_000,
                'fondpropreglobal'     => 200_000_000,
                'reportbudgetare'      => 100_000_000,
                'isvalide'             => 1,
                'statut'               => 1,
                'id_user'              => $admin->id,
                'effacer'              => 0,
                'version_exercice'     => 1,
            ]
        );

        Exercice::firstOrCreate(
            ['exercice' => '2025'],
            [
                'libelle'              => 'Exercice 2025 (clôturé)',
                'datedebut'            => '2025-01-01',
                'datefin'              => '2025-12-31',
                'budgetglobalinitial'  => 1_350_000_000,
                'dotationglobale'      => 1_100_000_000,
                'fondpropreglobal'     => 250_000_000,
                'isvalide'             => 1,
                'statut'               => 0,
                'id_user'              => $admin->id,
                'effacer'              => 0,
                'version_exercice'     => 1,
            ]
        );

        // Comptes bancaires
        $comptes = [
            ['code' => 'CPT-001', 'nom' => 'Compte Courant Principal BICEC', 'type' => 1, 'solde' => 425_000_000, 'rib' => '40028 00038 12345678901 23', 'responsable' => 'Direction Financière', 'domiciliation' => 'BICEC Libreville'],
            ['code' => 'CPT-002', 'nom' => 'Compte Épargne BICEC',            'type' => 2, 'solde' => 180_000_000, 'rib' => '40028 00038 98765432109 87', 'responsable' => 'Direction Financière', 'domiciliation' => 'BICEC Libreville'],
            ['code' => 'CPT-003', 'nom' => 'Caisse Petite (Espèces)',         'type' => 3, 'solde' => 2_500_000,   'rib' => null,                          'responsable' => 'Aminata Mensah',         'domiciliation' => 'Siège'],
        ];
        foreach ($comptes as $c) {
            Compte::firstOrCreate(
                ['code' => $c['code']],
                array_merge($c, ['effacer' => 0, 'gestionnaire' => 'Service Compta'])
            );
        }
    }

    /* ─────────────────────────────────────────────────── */
    /* RH                                                   */
    /* ─────────────────────────────────────────────────── */
    private function seedRH(User $admin): void
    {
        // Créer des employés liés aux users de l'annuaire
        $users = User::whereIn('email', [
            'admin@optimize.local', 'aminata.mensah@optimize.local', 'jp.obame@optimize.local',
            'sylvie.mba@optimize.local', 'patrick.nguema@optimize.local', 'linda.bouanga@optimize.local',
            'claude.ondo@optimize.local',
        ])->get();

        foreach ($users as $u) {
            Employee::firstOrCreate(
                ['user_id' => $u->id],
                [
                    'noms'           => $u->name,
                    'prenoms'        => $u->prenoms,
                    'matricule'      => $u->matricule ?? 'EMP-' . str_pad($u->id, 4, '0', STR_PAD_LEFT),
                    'date_naissance' => $u->date_naissance ?? now()->subYears(35)->toDateString(),
                    'lieu_naissance' => 'Libreville',
                    'nationalite'    => 'Gabonaise',
                    'sexe'           => fake()->randomElement(['M', 'F']),
                    'situation_matrimoniale' => fake()->randomElement(['Célibataire', 'Marié(e)']),
                    'email'          => $u->email,
                    'contact'        => $u->contact ?? '+241 06 00 00 00',
                    'date_embauche'  => $u->date_embauche ?? now()->subYears(2)->toDateString(),
                    'type_contrat'   => 'CDI',
                    'poste'          => $u->poste ?? 'Collaborateur',
                    'salaire_base'   => fake()->numberBetween(450_000, 2_500_000),
                    'statut'         => 1,
                ]
            );
        }

        // Quelques absences de démo
        $employee = Employee::first();
        if ($employee) {
            Absence::firstOrCreate(
                ['label' => 'Congé annuel 2026', 'employee_id' => $employee->id],
                [
                    'type_abscence' => 'Congé annuel',
                    'introduction'  => 'Congé annuel programmé.',
                    'description'   => 'Demande de congé du 15 au 30 août 2026.',
                    'debut'         => '2026-08-15 00:00:00',
                    'fin'           => '2026-08-30 23:59:59',
                    'statut'        => 1, // 1 = en attente
                ]
            );
            Absence::firstOrCreate(
                ['label' => 'Maladie', 'employee_id' => $employee->id],
                [
                    'type_abscence' => 'Maladie',
                    'description'  => 'Arrêt maladie 3 jours.',
                    'debut'        => now()->subDays(10)->setTime(0, 0)->format('Y-m-d H:i:s'),
                    'fin'          => now()->subDays(7)->setTime(23, 59)->format('Y-m-d H:i:s'),
                    'statut'       => 2, // approuvé
                    'valide_par'   => $admin->id,
                    'date_validation' => now()->subDays(8),
                ]
            );
        }
    }

    /* ─────────────────────────────────────────────────── */
    /* APPRO                                                */
    /* ─────────────────────────────────────────────────── */
    private function seedAppro(User $admin): void
    {
        // Fournisseurs
        $fournisseurs = [
            ['raison_sociale' => 'CloudTech SARL',     'sigle' => 'CTS',  'nif' => 'P000123456N', 'rccm' => 'GA-LBV-01-2020-B-12345', 'categorie' => 'Infrastructure cloud', 'note' => 4.5, 'email' => 'contact@cloudtech.ga', 'telephone' => '+241 01 11 22 33', 'adresse' => 'Glass, Libreville'],
            ['raison_sociale' => 'Bureau Plus',         'sigle' => 'BP',   'nif' => 'P000234567P', 'rccm' => 'GA-LBV-01-2018-B-23456', 'categorie' => 'Fournitures bureau',     'note' => 4.2, 'email' => 'commercial@bureauplus.ga', 'telephone' => '+241 01 22 33 44', 'adresse' => 'Mont-Bouët'],
            ['raison_sociale' => 'TechniMat SA',        'sigle' => 'TMSA', 'nif' => 'P000345678Q', 'rccm' => 'GA-LBV-01-2015-A-34567', 'categorie' => 'Matériel informatique',  'note' => 4.7, 'email' => 'commande@technimat.ga', 'telephone' => '+241 01 33 44 55', 'adresse' => 'Nzeng-Ayong'],
            ['raison_sociale' => 'GabonClean Services', 'sigle' => 'GCS',  'nif' => 'P000456789R', 'rccm' => 'GA-LBV-01-2019-B-45678', 'categorie' => 'Services nettoyage',     'note' => 3.8, 'email' => 'contact@gabonclean.ga', 'telephone' => '+241 01 44 55 66', 'adresse' => 'Akanda'],
        ];
        foreach ($fournisseurs as $f) {
            Fournisseur::firstOrCreate(
                ['nif' => $f['nif']],
                [
                    'raison_sociale'     => $f['raison_sociale'],
                    'sigle'              => $f['sigle'],
                    'rccm'               => $f['rccm'],
                    'adresse'            => $f['adresse'],
                    'ville'              => 'Libreville',
                    'pays'               => 'Gabon',
                    'telephone'          => $f['telephone'],
                    'email'              => $f['email'],
                    'categorie'          => $f['categorie'],
                    'note_evaluation'    => $f['note'],
                    'statut'             => 1,
                ]
            );
        }

        // Produits
        $produits = [
            ['code' => 'PRD-001', 'designation' => 'Ordinateur portable Dell Latitude', 'categorie' => 'Informatique', 'sous_categorie' => 'Ordinateurs', 'unite_mesure' => 'pièce', 'prix_unitaire' => 850_000, 'taux_tva' => 18, 'stock_actuel' => 12, 'stock_minimum' => 5, 'stock_maximum' => 30],
            ['code' => 'PRD-002', 'designation' => 'Imprimante HP LaserJet Pro',         'categorie' => 'Informatique', 'sous_categorie' => 'Imprimantes', 'unite_mesure' => 'pièce', 'prix_unitaire' => 320_000, 'taux_tva' => 18, 'stock_actuel' => 4,  'stock_minimum' => 3, 'stock_maximum' => 15],
            ['code' => 'PRD-003', 'designation' => 'Ramette papier A4 80g',              'categorie' => 'Bureautique',  'sous_categorie' => 'Papier',     'unite_mesure' => 'ramette', 'prix_unitaire' => 4_500, 'taux_tva' => 18, 'stock_actuel' => 80, 'stock_minimum' => 50, 'stock_maximum' => 200],
            ['code' => 'PRD-004', 'designation' => 'Cartouche encre noire HP 305',       'categorie' => 'Bureautique',  'sous_categorie' => 'Consommables', 'unite_mesure' => 'pièce', 'prix_unitaire' => 18_500, 'taux_tva' => 18, 'stock_actuel' => 25, 'stock_minimum' => 10, 'stock_maximum' => 60],
            ['code' => 'PRD-005', 'designation' => 'Chaise de bureau ergonomique',       'categorie' => 'Mobilier',     'sous_categorie' => 'Sièges',     'unite_mesure' => 'pièce', 'prix_unitaire' => 145_000, 'taux_tva' => 18, 'stock_actuel' => 8,  'stock_minimum' => 5, 'stock_maximum' => 25],
        ];
        foreach ($produits as $p) {
            Produit::firstOrCreate(['code' => $p['code']], array_merge($p, ['statut' => 1, 'emplacement' => 'Magasin central']));
        }

        // Commandes fournisseurs
        $fournisseur1 = Fournisseur::where('sigle', 'TMSA')->first();
        $fournisseur2 = Fournisseur::where('sigle', 'BP')->first();
        if ($fournisseur1) {
            CommandeFournisseur::firstOrCreate(
                ['numero_commande' => 'CMD-2026-001'],
                [
                    'fournisseur_id'         => $fournisseur1->id,
                    'date_commande'          => now()->subWeeks(2)->toDateString(),
                    'date_livraison_prevue'  => now()->addWeek()->toDateString(),
                    'montant_ht'             => 1_700_000,
                    'montant_tva'            => 306_000,
                    'montant_ttc'            => 2_006_000,
                    'mode_reglement'         => 'Virement 30 jours',
                    'conditions'             => 'Livraison franco-domicile, garantie 2 ans.',
                    'statut'                 => 1, // en cours
                ]
            );
        }
        if ($fournisseur2) {
            CommandeFournisseur::firstOrCreate(
                ['numero_commande' => 'CMD-2026-002'],
                [
                    'fournisseur_id'         => $fournisseur2->id,
                    'date_commande'          => now()->subWeeks(4)->toDateString(),
                    'date_livraison_prevue'  => now()->subWeek()->toDateString(),
                    'date_livraison_effective'=> now()->subWeek()->toDateString(),
                    'montant_ht'             => 450_000,
                    'montant_tva'            => 81_000,
                    'montant_ttc'            => 531_000,
                    'mode_reglement'         => 'Comptant',
                    'statut'                 => 2, // livré
                ]
            );
        }
    }

    /* ─────────────────────────────────────────────────── */
    /* MOYENS GÉNÉRAUX                                      */
    /* ─────────────────────────────────────────────────── */
    private function seedMG(User $admin): void
    {
        // Immobilisations
        $immos = [
            ['code' => 'IMM-001', 'designation' => 'Serveur Dell PowerEdge R750',      'categorie' => 'Matériel informatique', 'localisation' => 'Salle serveur',  'valeur_acquisition' => 12_500_000, 'duree_amortissement' => 5, 'date_acquisition' => '2025-03-15', 'etat' => 'Bon état',     'methode_amortissement' => 'Linéaire'],
            ['code' => 'IMM-002', 'designation' => 'Onduleur APC 5KVA',                'categorie' => 'Matériel informatique', 'localisation' => 'Salle serveur',  'valeur_acquisition' => 1_800_000,  'duree_amortissement' => 5, 'date_acquisition' => '2025-04-01', 'etat' => 'Bon état',     'methode_amortissement' => 'Linéaire'],
            ['code' => 'IMM-003', 'designation' => 'Véhicule Toyota Hilux',            'categorie' => 'Véhicule',              'localisation' => 'Parking',         'valeur_acquisition' => 28_000_000, 'duree_amortissement' => 5, 'date_acquisition' => '2024-11-10', 'etat' => 'Excellent',    'methode_amortissement' => 'Linéaire'],
            ['code' => 'IMM-004', 'designation' => 'Climatiseur split 24 000 BTU',     'categorie' => 'Équipement bâtiment',   'localisation' => 'Bureau B-201',   'valeur_acquisition' => 850_000,    'duree_amortissement' => 7, 'date_acquisition' => '2025-06-20', 'etat' => 'Bon état',     'methode_amortissement' => 'Linéaire'],
            ['code' => 'IMM-005', 'designation' => 'Mobilier réunion (table + 12 chaises)', 'categorie' => 'Mobilier',          'localisation' => 'Salle réunion A','valeur_acquisition' => 1_200_000,  'duree_amortissement' => 10,'date_acquisition' => '2024-09-05', 'etat' => 'Très bon état','methode_amortissement' => 'Linéaire'],
        ];
        foreach ($immos as $i) {
            // Calcul VNC simplifié
            $age = max(0, now()->year - (int) substr($i['date_acquisition'], 0, 4));
            $amortAnnuel = $i['valeur_acquisition'] / $i['duree_amortissement'];
            $vnc = max(0, $i['valeur_acquisition'] - ($amortAnnuel * $age));

            Immobilisation::firstOrCreate(
                ['code' => $i['code']],
                array_merge($i, ['valeur_nette_comptable' => $vnc, 'statut' => 1])
            );
        }

        // Dysfonctionnements
        $immo1 = Immobilisation::where('code', 'IMM-001')->first();
        $immo2 = Immobilisation::where('code', 'IMM-004')->first();

        if ($immo1) {
            Dysfonctionnement::firstOrCreate(
                ['label' => 'Bruit anormal ventilation serveur'],
                [
                    'description'        => 'Le ventilateur principal du serveur émet un bruit anormal depuis 3 jours.',
                    'declarant_id'       => $admin->id,
                    'immobilisation_id'  => $immo1->id,
                    'localisation'       => 'Salle serveur',
                    'priorite'           => 'haute',
                    'date_signalement'   => now()->subDays(3),
                    'statut'             => 1, // ouvert
                ]
            );
        }
        if ($immo2) {
            Dysfonctionnement::firstOrCreate(
                ['label' => 'Climatiseur ne refroidit plus'],
                [
                    'description'        => 'Le climatiseur fonctionne mais ne refroidit plus correctement.',
                    'declarant_id'       => $admin->id,
                    'immobilisation_id'  => $immo2->id,
                    'localisation'       => 'Bureau B-201',
                    'priorite'           => 'normale',
                    'date_signalement'   => now()->subDays(7),
                    'date_resolution'    => now()->subDays(2),
                    'statut'             => 3, // résolu
                ]
            );
        }
    }
}
