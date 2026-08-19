<?php

namespace Database\Seeders;

use App\Models\Ligne;
use App\Models\RubriqueOperation;
use App\Models\Titre;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Référentiels Finance — utilisable en démo et en production.
 *
 * Structure :
 *   Titre (famille budgétaire)
 *     ├─▶ Ligne (code analytique)
 *           ├─▶ Rubrique d'opération (détail rattaché à la ligne)
 *
 * Tout est idempotent (firstOrCreate par code/imputation).
 */
class FinanceReferentielsSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::where('email', 'admin@optimize.local')->value('id') ?? 1;

        // ─── TITRES (familles budgétaires) ────────────────────
        $titres = [
            ['imputation' => 'T01', 'libelle' => 'Rémunérations du personnel',  'type_ligne' => 'depense', 'seuil' => 500_000_000],
            ['imputation' => 'T02', 'libelle' => 'Fonctionnement',              'type_ligne' => 'depense', 'seuil' => 200_000_000],
            ['imputation' => 'T03', 'libelle' => 'Investissement',              'type_ligne' => 'depense', 'seuil' => 300_000_000],
            ['imputation' => 'T04', 'libelle' => 'Subventions et dotations',    'type_ligne' => 'recette', 'seuil' => 600_000_000],
            ['imputation' => 'T05', 'libelle' => 'Prestations de services',     'type_ligne' => 'recette', 'seuil' => 150_000_000],
            ['imputation' => 'T06', 'libelle' => 'Recettes diverses',           'type_ligne' => 'mixte',   'seuil' =>  50_000_000],
        ];
        $titreIds = [];
        foreach ($titres as $t) {
            $titre = Titre::firstOrCreate(
                ['imputation' => $t['imputation']],
                array_merge($t, ['id_user' => $userId])
            );
            $titreIds[$t['imputation']] = $titre->id;
        }

        // ─── LIGNES (codes analytiques) ───────────────────────
        // [titre_imputation, code_analytique_int, libelle, nature]
        $lignes = [
            // T01 Rémunérations
            ['T01', 1001, 'Salaires de base',                'fonctionnement'],
            ['T01', 1002, 'Primes et indemnités',            'fonctionnement'],
            ['T01', 1003, 'Charges sociales patronales',     'fonctionnement'],
            // T02 Fonctionnement
            ['T02', 2001, 'Fournitures de bureau',           'fonctionnement'],
            ['T02', 2002, 'Énergie (électricité, eau)',      'fonctionnement'],
            ['T02', 2003, 'Loyer et charges locatives',      'fonctionnement'],
            ['T02', 2004, 'Carburant et lubrifiants',        'fonctionnement'],
            ['T02', 2005, 'Communications',                  'fonctionnement'],
            ['T02', 2006, 'Maintenance et entretien',        'fonctionnement'],
            ['T02', 2007, 'Missions et déplacements',        'fonctionnement'],
            // T03 Investissement
            ['T03', 3001, 'Matériel informatique',           'investissement'],
            ['T03', 3002, 'Mobilier de bureau',              'investissement'],
            ['T03', 3003, 'Véhicules',                       'investissement'],
            ['T03', 3004, 'Constructions et aménagements',   'investissement'],
            // T04 Subventions reçues
            ['T04', 4001, 'Dotation de l\'État',             'fonctionnement'],
            ['T04', 4002, 'Subventions partenaires',         'fonctionnement'],
            // T05 Prestations
            ['T05', 5001, 'Honoraires de conseil',           'fonctionnement'],
            ['T05', 5002, 'Prestations de formation',        'fonctionnement'],
            ['T05', 5003, 'Études et audits',                'fonctionnement'],
            // T06 Recettes diverses
            ['T06', 6001, 'Produits financiers',             'fonctionnement'],
            ['T06', 6002, 'Recettes exceptionnelles',        'fonctionnement'],
        ];
        $ligneIds = []; // [code_analytique => ligne_id]
        foreach ($lignes as [$timp, $code, $libelle, $nature]) {
            $ligne = Ligne::firstOrCreate(
                ['id_codeanalytique' => $code],
                [
                    'id_titre' => $titreIds[$timp],
                    'libelle'  => $libelle,
                    'nature'   => $nature,
                    'id_user'  => $userId,
                ]
            );
            $ligneIds[$code] = $ligne->id;
        }

        // ─── RUBRIQUES D'OPÉRATIONS ────────────────────────────
        // [code, libelle, code_analytique_ligne, sens, categorie, ordre]
        $rubriques = [
            // 1001 Salaires de base
            ['RB-SA-01', 'Salaire brut mensuel',               1001, 'depense', 'salaire', 10],
            ['RB-SA-02', 'Rappel salaire',                     1001, 'depense', 'salaire', 20],
            // 1002 Primes et indemnités
            ['RB-PR-01', 'Prime de rendement',                 1002, 'depense', 'prime', 10],
            ['RB-PR-02', 'Prime d\'ancienneté',                1002, 'depense', 'prime', 20],
            ['RB-PR-03', 'Indemnité de transport',             1002, 'depense', 'prime', 30],
            ['RB-PR-04', 'Indemnité de logement',              1002, 'depense', 'prime', 40],
            // 1003 Charges sociales patronales (exemple demandé : CNSS + CNAMGS)
            ['RB-CS-01', 'Cotisation CNSS (part patronale)',   1003, 'depense', 'charges_sociales', 10],
            ['RB-CS-02', 'Cotisation CNAMGS (part patronale)', 1003, 'depense', 'charges_sociales', 20],
            ['RB-CS-03', 'Taxe complémentaire sur salaires',   1003, 'depense', 'charges_sociales', 30],
            // 2001 Fournitures bureau
            ['RB-FB-01', 'Papier A4 (rame)',                   2001, 'depense', 'fourniture', 10],
            ['RB-FB-02', 'Stylos, marqueurs, surligneurs',     2001, 'depense', 'fourniture', 20],
            ['RB-FB-03', 'Cartouches d\'encre / toners',       2001, 'depense', 'fourniture', 30],
            ['RB-FB-04', 'Classeurs et archivage',             2001, 'depense', 'fourniture', 40],
            // 2002 Énergie
            ['RB-EN-01', 'Facture SEEG — électricité',         2002, 'depense', 'energie',    10],
            ['RB-EN-02', 'Facture SEEG — eau',                 2002, 'depense', 'energie',    20],
            ['RB-EN-03', 'Groupe électrogène (carburant)',     2002, 'depense', 'energie',    30],
            // 2003 Loyer
            ['RB-LO-01', 'Loyer mensuel — siège',              2003, 'depense', 'loyer',      10],
            ['RB-LO-02', 'Charges copropriété',                2003, 'depense', 'loyer',      20],
            // 2004 Carburant
            ['RB-CA-01', 'Carburant — véhicule de service',    2004, 'depense', 'carburant',  10],
            ['RB-CA-02', 'Carburant — déplacements mission',   2004, 'depense', 'carburant',  20],
            // 2005 Communications
            ['RB-CO-01', 'Téléphonie mobile (Airtel/Moov)',    2005, 'depense', 'comm',       10],
            ['RB-CO-02', 'Abonnement internet (Gabon Telecom)',2005, 'depense', 'comm',       20],
            ['RB-CO-03', 'Hébergement web et noms de domaine', 2005, 'depense', 'comm',       30],
            // 2006 Maintenance
            ['RB-MA-01', 'Maintenance climatiseurs',           2006, 'depense', 'maintenance',10],
            ['RB-MA-02', 'Maintenance véhicules',              2006, 'depense', 'maintenance',20],
            ['RB-MA-03', 'Maintenance informatique',           2006, 'depense', 'maintenance',30],
            // 2007 Missions
            ['RB-MI-01', 'Frais d\'hébergement (hôtel)',       2007, 'depense', 'mission',    10],
            ['RB-MI-02', 'Indemnités de mission',              2007, 'depense', 'mission',    20],
            ['RB-MI-03', 'Billets d\'avion / transport',       2007, 'depense', 'mission',    30],
            // 3001 Matériel informatique
            ['RB-MI-04', 'Ordinateurs portables',              3001, 'depense', 'investissement', 10],
            ['RB-MI-05', 'Imprimantes / scanners',             3001, 'depense', 'investissement', 20],
            ['RB-MI-06', 'Serveurs et équipements réseau',     3001, 'depense', 'investissement', 30],
            // 3002 Mobilier
            ['RB-MO-01', 'Bureaux et tables',                  3002, 'depense', 'mobilier',   10],
            ['RB-MO-02', 'Chaises de bureau',                  3002, 'depense', 'mobilier',   20],
            ['RB-MO-03', 'Armoires et rangements',             3002, 'depense', 'mobilier',   30],
            // 4001 Dotation État
            ['RB-DE-01', 'Tranche trimestrielle dotation État', 4001, 'recette', 'dotation', 10],
            // 4002 Subventions
            ['RB-SU-01', 'Subvention bailleur de fonds',       4002, 'recette', 'subvention', 10],
            ['RB-SU-02', 'Convention partenaire institutionnel',4002, 'recette', 'subvention', 20],
            // 5001 Honoraires conseil
            ['RB-HO-01', 'Mission de conseil — jour homme',    5001, 'recette', 'prestation', 10],
            ['RB-HO-02', 'Accompagnement stratégique forfait', 5001, 'recette', 'prestation', 20],
            // 5002 Formation
            ['RB-FO-01', 'Session de formation — par stagiaire',5002, 'recette', 'prestation', 10],
            ['RB-FO-02', 'Formation intra-entreprise forfait', 5002, 'recette', 'prestation', 20],
        ];

        $created = 0;
        foreach ($rubriques as [$code, $libelle, $codeAna, $sens, $cat, $ordre]) {
            $rub = RubriqueOperation::firstOrCreate(
                ['code' => $code],
                [
                    'libelle'         => $libelle,
                    'ligne_id'        => $ligneIds[$codeAna] ?? null,
                    'sens'            => $sens,
                    'categorie'       => $cat,
                    'ordre_affichage' => $ordre,
                    'statut'          => 1,
                    'created_by'      => $userId,
                ]
            );
            if ($rub->wasRecentlyCreated) $created++;
        }

        $this->command?->info(sprintf(
            'Référentiels Finance : %d titres, %d lignes, %d/%d rubriques nouvelles.',
            count($titres),
            count($lignes),
            $created,
            count($rubriques)
        ));
    }
}
