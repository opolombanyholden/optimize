<?php

namespace Database\Seeders;

use App\Models\BudgetLigne;
use App\Models\Client;
use App\Models\Exercice;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Ligne;
use App\Models\ModificationBudgetaire;
use App\Models\OperationFinanciere;
use App\Models\OperationFinanciereDetail;
use App\Models\RubriqueOperation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder de démo Finance : clients, lignes budgétaires alimentées,
 * modifications, factures, et opérations financières complètes.
 *
 * Dépend de :
 *   - FinanceReferentielsSeeder (titres, lignes, rubriques)
 *   - ErpDemoSeeder (exercices, fournisseurs, comptes — optionnel)
 *
 * Idempotent : firstOrCreate sur tous les objets uniques.
 */
class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::where('email', 'admin@optimize.local')->value('id') ?? 1;

        // ─── EXERCICE EN COURS (créé si absent) ───────────────
        $exercice = Exercice::firstOrCreate(
            ['exercice' => '2026'],
            [
                'libelle'             => 'Exercice 2026',
                'statut'              => 2, // en cours
                'datedebut'           => '2026-01-01',
                'datefin'             => '2026-12-31',
                'dotationglobale'     => 800_000_000,
                'fondpropreglobal'    => 150_000_000,
                'budgetglobalinitial' => 950_000_000,
                'id_user'             => $userId,
            ]
        );

        // ─── CLIENTS ──────────────────────────────────────────
        $clients = [
            [
                'code'           => 'CLI-001',
                'raison_sociale' => 'Ministère de l\'Économie et des Finances',
                'forme_juridique'=> 'Établissement public',
                'nif'            => 'NIF-MINECO-001',
                'adresse'        => 'BP 165 Libreville',
                'ville'          => 'Libreville',
                'pays'           => 'Gabon',
                'telephone'      => '+241 01 76 00 00',
                'email'          => 'contact@mineco.gov.ga',
                'contact_nom'    => 'Direction des affaires financières',
            ],
            [
                'code'           => 'CLI-002',
                'raison_sociale' => 'ONG Partenariat Développement Gabon',
                'forme_juridique'=> 'Association',
                'nif'            => 'NIF-PDG-2026',
                'adresse'        => 'Quartier Glass, BP 4218',
                'ville'          => 'Libreville',
                'pays'           => 'Gabon',
                'telephone'      => '+241 01 44 22 11',
                'email'          => 'contact@pdg.org',
                'contact_nom'    => 'Marie OBAME',
                'contact_email'  => 'marie.obame@pdg.org',
            ],
            [
                'code'           => 'CLI-003',
                'raison_sociale' => 'Entreprise Forestière du Gabon SARL',
                'forme_juridique'=> 'SARL',
                'nif'            => 'NIF-EFG-9988',
                'rccm'           => 'RCCM/LBV/2018-B-1234',
                'adresse'        => 'Zone industrielle d\'Oloumi',
                'ville'          => 'Libreville',
                'pays'           => 'Gabon',
                'telephone'      => '+241 06 12 34 56',
                'email'          => 'contact@efg.ga',
                'contact_nom'    => 'Pierre MOUSSAVOU',
                'rib'            => 'GA21 4002 8000 38 12345 67890 12 34',
                'banque'         => 'BICIG',
            ],
        ];
        $clientIds = [];
        foreach ($clients as $c) {
            $cli = Client::firstOrCreate(['code' => $c['code']], array_merge($c, ['statut' => 1, 'created_by' => $userId]));
            $clientIds[$c['code']] = $cli->id;
        }

        // ─── LIGNES BUDGÉTAIRES ALIMENTÉES ────────────────────
        // On rattache 5 lignes budgétaires à des codes analytiques du référentiel
        $budgetLignes = [
            // [id_budgetligne, code_analytique, libelle, dotation, fonds_propres, engagement_initial]
            ['BL-26-001', 1001, 'Salaires personnel permanent',      350_000_000,        0,  150_000_000],
            ['BL-26-002', 2001, 'Fournitures et consommables',         15_000_000,  5_000_000,   4_500_000],
            ['BL-26-003', 2003, 'Loyer siège social',                  24_000_000,        0,  12_000_000],
            ['BL-26-004', 2005, 'Communications et internet',           8_000_000,        0,   3_200_000],
            ['BL-26-005', 3001, 'Équipement informatique',             50_000_000, 20_000_000,        0],
            ['BL-26-006', 2007, 'Missions et déplacements',            30_000_000,        0,  18_500_000],
            ['BL-26-007', 2006, 'Maintenance générale',                12_000_000,  3_000_000,   1_800_000],
        ];

        $budgetLigneIds = [];
        foreach ($budgetLignes as [$ref, $ca, $libelle, $dotation, $fp, $engagement]) {
            $ligne = Ligne::where('id_codeanalytique', $ca)->first();
            $bl = BudgetLigne::firstOrCreate(
                ['id_budgetligne' => $ref],
                [
                    'id_exercicebudgetaire'    => $exercice->id,
                    'exercice'                 => $exercice->exercice,
                    'id_codeanalytique'        => $ligne?->id, // FK Ligne
                    'id_famillecodeanalytique' => $ligne?->id_titre,
                    'commentaire'              => $libelle,
                    'budgetligne'              => $dotation + $fp,
                    'dotation_etat'            => $dotation,
                    'fonds_propres'            => $fp,
                    'engagement'               => $engagement,
                    'isvalide'                 => 1, // validée
                    'id_user'                  => $userId,
                ]
            );
            $budgetLigneIds[$ref] = $bl->id;
        }

        // ─── MODIFICATIONS BUDGÉTAIRES (workflow varié) ───────
        // 1. Une approuvée prête à appliquer
        ModificationBudgetaire::firstOrCreate(
            ['objetmodification' => 'Renforcement budget communications T2'],
            [
                'exercice_id'                 => $exercice->id,
                'type_modification'           => 'transfert',
                'budget_ligne_source_id'      => $budgetLigneIds['BL-26-005'], // matériel info
                'budget_ligne_destination_id' => $budgetLigneIds['BL-26-004'], // communications
                'montant_modification'        => 2_000_000,
                'commentaire'                 => 'Mise à niveau de l\'abonnement fibre + couverture des dépassements',
                'id_user'                     => $userId,
                'statut'                      => 2, // approuvée
                'soumis_par'                  => $userId,
                'soumis_at'                   => now()->subDays(8),
                'approuve_par'                => $userId,
                'approuve_at'                 => now()->subDays(3),
            ]
        );
        // 2. Une soumise en attente d'approbation
        ModificationBudgetaire::firstOrCreate(
            ['objetmodification' => 'Apport supplémentaire maintenance climatisation'],
            [
                'exercice_id'                 => $exercice->id,
                'type_modification'           => 'ajout',
                'budget_ligne_destination_id' => $budgetLigneIds['BL-26-007'],
                'montant_modification'        => 1_500_000,
                'commentaire'                 => 'Vague de chaleur de juin — maintenance des climatiseurs anciens',
                'id_user'                     => $userId,
                'statut'                      => 1, // soumise
                'soumis_par'                  => $userId,
                'soumis_at'                   => now()->subDays(2),
            ]
        );
        // 3. Une appliquée historique
        $modAppliquee = ModificationBudgetaire::firstOrCreate(
            ['objetmodification' => 'Transfert vers missions terrain'],
            [
                'exercice_id'                 => $exercice->id,
                'type_modification'           => 'transfert',
                'budget_ligne_source_id'      => $budgetLigneIds['BL-26-002'],
                'budget_ligne_destination_id' => $budgetLigneIds['BL-26-006'],
                'montant_modification'        => 3_000_000,
                'commentaire'                 => 'Réallocation pour missions de terrain T1',
                'id_user'                     => $userId,
                'statut'                      => 3, // appliquée
                'soumis_par'                  => $userId,
                'soumis_at'                   => now()->subDays(60),
                'approuve_par'                => $userId,
                'approuve_at'                 => now()->subDays(58),
                'applique_par'                => $userId,
                'applique_at'                 => now()->subDays(57),
            ]
        );
        // Refléter les soldes côté lignes
        BudgetLigne::find($budgetLigneIds['BL-26-002'])?->update(['transfert' => -3_000_000]);
        BudgetLigne::find($budgetLigneIds['BL-26-006'])?->update(['transfert' => 3_000_000]);

        // ─── FACTURES ─────────────────────────────────────────
        $fournisseur = Fournisseur::first() ?: Fournisseur::create([
            'raison_sociale' => 'BUREAU MODERNE GABON SARL',
            'email'          => 'contact@bureau-moderne.ga',
            'statut'         => 1,
        ]);

        // Facture fournisseur (dépense) validée
        $facDep = Facture::firstOrCreate(
            ['numero' => 'FF-2026-04-001'],
            [
                'sens'             => 'depense',
                'tiers_type'       => 'fournisseur',
                'tiers_id'         => $fournisseur->id,
                'exercice_id'      => $exercice->id,
                'date_emission'    => '2026-04-10',
                'date_echeance'    => '2026-05-10',
                'reference_externe'=> 'FACT-BMG-1245',
                'objet'            => 'Fournitures de bureau T2',
                'montant_ht'       => 1_500_000,
                'taux_tva'         => 18,
                'montant_tva'      => 270_000,
                'montant_ttc'      => 1_770_000,
                'statut'           => 1, // validée
                'valide_par'       => $userId,
                'valide_at'        => Carbon::parse('2026-04-12'),
                'commentaire'      => 'Reçu et conforme au bon de commande BC-2026-04-007',
                'created_by'       => $userId,
            ]
        );

        // Facture client (recette) — partiellement réglée
        $facRec = Facture::firstOrCreate(
            ['numero' => 'FC-2026-03-001'],
            [
                'sens'             => 'recette',
                'tiers_type'       => 'client',
                'tiers_id'         => $clientIds['CLI-002'],
                'exercice_id'      => $exercice->id,
                'date_emission'    => '2026-03-15',
                'date_echeance'    => '2026-04-15',
                'objet'            => 'Mission de conseil stratégique T1',
                'montant_ht'       => 5_000_000,
                'taux_tva'         => 18,
                'montant_tva'      => 900_000,
                'montant_ttc'      => 5_900_000,
                'montant_regle'    => 2_950_000, // 50%
                'statut'           => 2, // partiellement réglée
                'valide_par'       => $userId,
                'valide_at'        => Carbon::parse('2026-03-16'),
                'created_by'       => $userId,
            ]
        );

        // ─── OPÉRATIONS FINANCIÈRES ────────────────────────────
        // 1. Ordre de dépense exécuté avec détails (achat fournitures)
        $op1 = OperationFinanciere::firstOrCreate(
            ['numero' => 'OD-2026-04-001'],
            [
                'type_operation'   => 'depense',
                'exercice_id'      => $exercice->id,
                'budget_ligne_id'  => $budgetLigneIds['BL-26-002'],
                'tiers_type'       => 'fournisseur',
                'tiers_id'         => $fournisseur->id,
                'facture_id'       => $facDep->id,
                'date_operation'   => '2026-04-12',
                'objet'            => 'Achat fournitures bureau — avril 2026',
                'montant'          => 1_770_000,
                'mode_reglement'   => 'virement',
                'reference_reglement' => 'VIR-2026-04-128',
                'commentaire'      => 'Règlement de la facture FF-2026-04-001',
                'statut'           => 3, // exécutée
                'soumis_par'       => $userId,
                'soumis_at'        => Carbon::parse('2026-04-12 09:00'),
                'approuve_par'     => $userId,
                'approuve_at'      => Carbon::parse('2026-04-13 11:30'),
                'execute_par'      => $userId,
                'execute_at'       => Carbon::parse('2026-04-14 15:00'),
                'created_by'       => $userId,
            ]
        );
        // Détails de l'opération 1
        if ($op1->details()->count() === 0) {
            $rubs = [
                ['code' => 'RB-FB-01', 'qte' => 50,  'pu' => 4_500,  'libelle' => 'Papier A4 80g (rame de 500)'],
                ['code' => 'RB-FB-02', 'qte' => 100, 'pu' => 1_500,  'libelle' => 'Stylos bille assortis'],
                ['code' => 'RB-FB-03', 'qte' => 10,  'pu' => 75_000, 'libelle' => 'Toners imprimante HP LJ-3045'],
                ['code' => 'RB-FB-04', 'qte' => 30,  'pu' => 4_500,  'libelle' => 'Classeurs rigides 8cm'],
            ];
            $rang = 1;
            foreach ($rubs as $d) {
                $r = RubriqueOperation::where('code', $d['code'])->first();
                OperationFinanciereDetail::create([
                    'operation_financiere_id' => $op1->id,
                    'rubrique_id'             => $r?->id,
                    'libelle'                 => $d['libelle'],
                    'quantite'                => $d['qte'],
                    'prix_unitaire'           => $d['pu'],
                    'montant'                 => $d['qte'] * $d['pu'],
                    'ordre'                   => $rang++,
                ]);
            }
            // Recalcule du total réel des détails (peut différer du HT facture)
            $total = $op1->details()->sum('montant');
            if ($total > 0) $op1->update(['montant' => $total]);
        }

        // 2. Ordre de recette en attente (paiement client partiel)
        OperationFinanciere::firstOrCreate(
            ['numero' => 'OR-2026-04-001'],
            [
                'type_operation'   => 'recette',
                'exercice_id'      => $exercice->id,
                'tiers_type'       => 'client',
                'tiers_id'         => $clientIds['CLI-002'],
                'facture_id'       => $facRec->id,
                'date_operation'   => '2026-04-05',
                'objet'            => 'Premier acompte 50% — mission conseil PDG',
                'montant'          => 2_950_000,
                'mode_reglement'   => 'virement',
                'reference_reglement' => 'VIR-IN-2026-04-027',
                'statut'           => 2, // approuvée, prête à exécuter
                'soumis_par'       => $userId,
                'soumis_at'        => Carbon::parse('2026-04-04 10:00'),
                'approuve_par'     => $userId,
                'approuve_at'      => Carbon::parse('2026-04-05 09:30'),
                'created_by'       => $userId,
            ]
        );

        // 3. Ordre de dépense en brouillon (à finaliser)
        $op3 = OperationFinanciere::firstOrCreate(
            ['numero' => 'OD-2026-06-001'],
            [
                'type_operation'   => 'depense',
                'exercice_id'      => $exercice->id,
                'budget_ligne_id'  => $budgetLigneIds['BL-26-006'],
                'date_operation'   => '2026-06-10',
                'objet'            => 'Mission terrain Port-Gentil — équipe terrain (3 personnes)',
                'montant'          => 0, // sera calculé via détails
                'commentaire'      => 'Du 10 au 14 juin 2026',
                'statut'           => 0,
                'created_by'       => $userId,
            ]
        );
        // Détails brouillon
        if ($op3->details()->count() === 0) {
            $rubs = [
                ['code' => 'RB-MI-01', 'qte' => 12, 'pu' => 45_000,  'libelle' => 'Hôtel — 4 nuitées × 3 personnes'],
                ['code' => 'RB-MI-02', 'qte' => 15, 'pu' => 25_000,  'libelle' => 'Indemnités — 5 jours × 3 personnes'],
                ['code' => 'RB-MI-03', 'qte' => 6,  'pu' => 35_000,  'libelle' => 'Billets train aller-retour × 3'],
            ];
            $rang = 1;
            $total = 0;
            foreach ($rubs as $d) {
                $r = RubriqueOperation::where('code', $d['code'])->first();
                $mt = $d['qte'] * $d['pu'];
                OperationFinanciereDetail::create([
                    'operation_financiere_id' => $op3->id,
                    'rubrique_id'             => $r?->id,
                    'libelle'                 => $d['libelle'],
                    'quantite'                => $d['qte'],
                    'prix_unitaire'           => $d['pu'],
                    'montant'                 => $mt,
                    'ordre'                   => $rang++,
                ]);
                $total += $mt;
            }
            $op3->update(['montant' => $total]);
        }

        $this->command?->info(sprintf(
            'Démo Finance : %d clients, %d budget lignes, %d modifications, %d factures, %d opérations.',
            count($clients),
            count($budgetLignes),
            3,
            2,
            3
        ));
    }
}
