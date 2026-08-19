<?php

namespace Database\Seeders;

use App\Models\Compte;
use App\Models\Entite;
use App\Models\Exercice;
use App\Models\Finance\Budget;
use App\Models\Finance\BudgetSource;
use App\Models\Finance\Config as FinanceConfig;
use App\Models\Finance\Source;
use App\Models\Finance\Transaction;
use App\Models\Finance\TransactionDetail;
use App\Models\Ligne;
use App\Models\ModeReglement;
use App\Models\Titre;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder Finance V2 — Données OHADA réelles extraites du SQL initial (optimize_finance.sql).
 *
 * Références au CdC initial :
 *  - Titres 60-66  : plan comptable OHADA (dépenses par famille)
 *  - Section 1     : Recettes (titre spécial)
 *  - Section Invest: Recettes d'investissement
 *  - Lignes 7xxx   : nature recettes (7313 Subvention Etat, 702 Recettes propres…)
 *  - Lignes 6xxx   : nature dépenses (601101 Fournitures, 611101 Missions…)
 *  - Sources       : FP, RB, ETAT
 *  - ModeReglements: CHQ, NUM, VB
 *  - Entite        : ANPI-Gabon (client cible du CdC)
 *
 * Idempotent (firstOrCreate par code).
 */
class FinanceV2InitialSeeder extends Seeder
{
    public function run(): void
    {
        // Assure qu'un user existe en env test (les BD de test sont fresh)
        $userId = User::where('email', 'admin@optimize.local')->value('id');
        if (!$userId) {
            $userId = User::first()?->id;
            if (!$userId) {
                $u = User::create([
                    'name' => 'Admin V2', 'prenoms' => 'Test',
                    'email' => 'admin-v2-seed@test.local',
                    'password' => bcrypt('secret'),
                    'contact' => '000000',
                    'statut' => 1,
                ]);
                $userId = $u->id;
            }
        }

        // ─── 1. SOURCES DE FINANCEMENT (extraites du SQL initial) ────
        $sources = [
            ['code' => 'FP',   'label' => 'Fonds propres'],
            ['code' => 'RB',   'label' => 'Reports budgétaires'],
            ['code' => 'ETAT', 'label' => "Dotation de l'État"],
        ];
        foreach ($sources as $s) {
            Source::firstOrCreate(['code' => $s['code']], $s);
        }

        // ─── 2. MODES DE RÈGLEMENT (extraits du SQL initial) ────
        $modes = [
            ['code' => 'CHQ', 'label' => 'Chèque'],
            ['code' => 'NUM', 'label' => 'Numéraire'],
            ['code' => 'VB',  'label' => 'Virement Bancaire'],
        ];
        foreach ($modes as $m) {
            ModeReglement::firstOrCreate(['code' => $m['code']], $m);
        }

        // ─── 3. ENTITE (client cible du CdC : ANPI-Gabon) ────
        $entite = Entite::firstOrCreate(
            ['code' => 'ANPI'],
            [
                'libelle'     => 'ANPI-Gabon', // legacy NOT NULL
                'label'       => 'ANPI-Gabon',
                'description' => 'Agence Nationale de Promotion des Investissements du Gabon',
            ]
        );

        // ─── 4. TITRES OHADA (extraits du SQL initial) ────
        $titres = [
            ['code' => '',   'label' => 'SECTION 1 : RECETTES'],
            ['code' => '60', 'label' => 'Dépenses : Achats de biens et de produits de consommation'],
            ['code' => '61', 'label' => "Dépenses : Achats de services"],
            ['code' => '62', 'label' => 'Dépenses : Services bancaires'],
            ['code' => '64', 'label' => 'Dépenses : Transferts courants'],
            ['code' => '65', 'label' => 'Dépenses : Autres droits'],
            ['code' => '66', 'label' => 'Dépenses : Charges de personnel'],
            ['code' => '',   'label' => "SECTION D'INVESTISSEMENT : RECETTES"],
        ];
        $titreIds = [];
        foreach ($titres as $t) {
            $typeLigne = str_contains(strtoupper($t['label']), 'RECETTE') ? 'recette' : 'depense';
            $titre = Titre::updateOrCreate(
                ['code' => $t['code'], 'label' => $t['label']],
                array_merge($t, [
                    'imputation' => $t['code'],
                    'libelle'    => $t['label'],
                    'type_ligne' => $typeLigne, // legacy NOT NULL
                    'id_user'    => $userId,
                ])
            );
            $titreIds[$t['label']] = $titre->id;
        }

        // ─── 5. LIGNES OHADA (extraites du SQL initial, natures = codes OHADA réels) ────
        // Format : [code, label, titre_index (dans $titres), type (0=depense, 1=recette)]
        $lignes = [
            // Recettes (titre_id : SECTION 1 : RECETTES → index 0)
            ['7313', 'Subvention de fonctionnement État',     0, 1],
            ['7391', "Subvention des Emplois État",           0, 1],
            ['702',  'Recettes propres Anpi-Gabon',           0, 1],
            ['7021', 'Produits DFDE',                          0, 1],
            ['7022', 'Produits DEAE',                          0, 1],
            ['7023', 'Produits DMC',                           0, 1],
            ['7024', 'Produits DAF',                           0, 1],
            ['7025', 'Produits SI',                            0, 1],
            ['7089', 'Autres produits Loyers',                 0, 1],
            ['7546', 'Produits divers',                        0, 1],

            // Dépenses Titre 60 (Achats de biens)
            ['601101', 'Fournitures',                          1, 0],
            ['601101', 'Documents périodiques',                1, 0],
            ['601102', 'Fournitures informatiques',            1, 0],
            ['601103', 'Papeterie',                            1, 0],
            ['601104', 'Fournitures audio-visuelles',          1, 0],
            ['601105', 'Imprimés spéciaux',                    1, 0],
            ['601106', "Fournitures d'imprimerie",             1, 0],
            ['601107', 'Fournitures de bureaux diverses',      1, 0],
            ['601108', "Produits et fournitures d'entretien de bureaux", 1, 0],
            ['601201', 'Fournitures et entretien - véhicules de fonctions', 1, 0],
            ['601202', 'Fournitures et entretien - autres véhicules', 1, 0],
            ['602402', 'Pâtisserie',                           1, 0],
            ['602502', 'Boissons',                             1, 0],
            ['602509', 'Produits alimentaires divers',         1, 0],

            // Dépenses Titre 61 (Achats de services)
            ['611101', 'Frais de mission au Gabon',            2, 0],
            ['611102', 'Frais de mission hors du Gabon',       2, 0],
            ['611201', 'Frais de déplacement au Gabon',        2, 0],
            ['611202', 'Frais de déplacement hors du Gabon',   2, 0],
            ['611311', 'Transport terrestre',                  2, 0],
            ['611401', 'Accueil et réception hôtes de marque', 2, 0],
            ['611402', 'Autres réceptions et restaurant',      2, 0],
            ['611403', 'Fournitures accueil et réception',     2, 0],
            ['611405', "Autres frais d'hôtellerie",            2, 0],
            ['611407', 'Location salle de conférence',         2, 0],
            ['612102', 'Location bureaux',                     2, 0],
            ['613305', 'Service gardiennage - Bâtiments divers', 2, 0],
            ['613405', 'Convention nettoyage - Bâtiments divers', 2, 0],
            ['613521', 'Honoraires',                           2, 0],
            ['613532', 'Jetons de présence',                   2, 0],
            ['614101', 'Entretien et réparation - véhicules divers', 2, 0],
            ['614309', 'Entretien et réparation - équipements divers', 2, 0],
            ['615102', 'Assurances véhicules divers',          2, 0],
            ['615109', 'Assurances Bâtiments et équipements divers', 2, 0],
        ];
        $ligneIds = [];
        foreach ($lignes as [$code, $label, $titreIdx, $type]) {
            $titreLabel = $titres[$titreIdx]['label'];
            $ligne = Ligne::updateOrCreate(
                ['code' => $code, 'label' => $label],
                [
                    'titre_id'          => $titreIds[$titreLabel] ?? null,
                    'id_titre'          => $titreIds[$titreLabel] ?? null, // legacy
                    'libelle'           => $label,
                    'nature'            => $code,
                    'status'            => 1,
                    'type'              => $type,
                    'id_user'           => $userId,
                    'id_codeanalytique' => hexdec(substr(md5($code . $label), 0, 6)) % 999999, // conservé pour legacy
                ]
            );
            $ligneIds[] = $ligne->id;
        }

        // ─── 6. EXERCICE 2026 (adapté aux nouveaux champs) ────
        $exercice2026 = Exercice::firstOrCreate(
            ['exercice' => '2026'],
            [
                'code'                => 'EX2026',
                'label'               => 'Exercice 2026',
                'libelle'             => 'Exercice 2026',
                'debut'               => '2026-01-01',
                'fin'                 => '2026-12-31',
                'datedebut'           => '2026-01-01',
                'datefin'             => '2026-12-31',
                'budgetglobal'        => 950_000_000,
                'budgetglobalinitial' => 950_000_000,
                'budgetglobalrestant' => 950_000_000,
                'global'              => true,
                'status'              => 0, // planification
                'statut'              => 1, // legacy
                'entite_id'           => $entite->id,
                'id_user'             => $userId,
            ]
        );

        // ─── 7. CONFIGS de base (paramétrage clé/valeur) ────
        $configs = [
            ['key' => 'devise_defaut',          'value' => 'XAF'],
            ['key' => 'seuil_alerte_pct',       'value' => '90'],
            ['key' => 'mode_reglement_defaut',  'value' => 'VB'],
            ['key' => 'source_defaut',          'value' => 'ETAT'],
        ];
        foreach ($configs as $c) {
            FinanceConfig::firstOrCreate(['key' => $c['key']], $c);
        }

        $this->command?->info(sprintf(
            'Finance V2 OHADA : %d sources, %d modes, %d titres, %d lignes, 1 exercice, %d configs.',
            count($sources), count($modes), count($titres), count($lignes), count($configs)
        ));
    }
}
