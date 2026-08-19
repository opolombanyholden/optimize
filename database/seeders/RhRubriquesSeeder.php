<?php

namespace Database\Seeders;

use App\Models\Referentiel\GroupeRubrique;
use App\Models\Rubrique;
use Illuminate\Database\Seeder;

/**
 * Catalogue des rubriques de paie selon le modèle ANPI-Gabon.
 * Codification :
 *   1xxx — Rémunérations (gains)
 *   2xxx — Cotisations / précomptes (retenues)
 *   5xxx — Indemnités non imposables
 * Bases de calcul nommées (texte libre) — précisent l'assiette à appliquer.
 */
class RhRubriquesSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Groupes (modèle ANPI : 7 groupes officiels) ──
        $groupes = [
            ['code' => '01', 'libelle' => 'Rémunérations de base',     'ordre' => 1],
            ['code' => '02', 'libelle' => 'Aide au logement',          'ordre' => 2],
            ['code' => '03', 'libelle' => 'Aide au transport',         'ordre' => 3],
            ['code' => '04', 'libelle' => 'Rémunération de spécialité','ordre' => 4],
            ['code' => '05', 'libelle' => 'Rémunération de représentation', 'ordre' => 5],
            ['code' => '06', 'libelle' => 'Prestations sociales',      'ordre' => 6],
            ['code' => '07', 'libelle' => 'Allocations de compensation','ordre' => 7],
            ['code' => '08', 'libelle' => 'Cotisations & précomptes',  'ordre' => 8],
        ];
        $groupesIds = [];
        foreach ($groupes as $g) {
            $row = GroupeRubrique::updateOrCreate(['code' => $g['code']], array_merge($g, ['statut' => 1]));
            $groupesIds[$g['code']] = $row->id;
        }

        // ─── Rubriques (codification ANPI) ──
        // [code, libelle, libelle_court, type, base_calcul, taux, valeur2 (plafond), montant_fixe, formule, base_calcul_libelle, groupe, imposable, cotisable, ordre]
        $rubriques = [
            // ─── 1xxx : RÉMUNÉRATIONS (groupe 01) ──
            ['1000', 'Salaire de base',          'SAL_BASE', 'gain',       'formule',     0,    0, 0, 'salaire_base', 'Salaire de base contractuel', '01', true, true, 1000],
            ['1400', 'Prime d\'ancienneté',      'PRIME_ANC','gain',       'pourcentage', 2,    0, 0, null,           'Salaire de base', '01', true, true, 1400],
            ['1500', 'Prime de responsabilité',  'PRIME_RES','gain',       'fixe',        0,    0, 0, null,           '—', '01', true, true, 1500],
            ['1600', 'Prime de rendement',       'PRIME_REN','gain',       'fixe',        0,    0, 0, null,           '—', '01', true, true, 1600],
            ['1700', 'Heures supplémentaires 25%','HSUP_25', 'gain',       'formule',     0,    0, 0, 'heures_sup',   'Heures sup × tarif horaire × 1.25', '01', true, true, 1700],

            // ─── 5xxx : INDEMNITÉS NON IMPOSABLES (groupes 02/03/05) ──
            ['5500', 'Indemnité de logement',    'IND_LOG',  'gain',       'fixe',        0,    0, 0, null, '—', '02', false, false, 5500],
            ['5700', 'Indemnité de transport',   'IND_TRANS','gain',       'fixe',        0,    0, 30000, null, '—', '03', false, false, 5700],
            ['5720', 'Prime de panier',          'PANIER',   'gain',       'fixe',        0,    0, 27000, null, '—', '03', false, false, 5720],
            ['5760', 'Indemnité entretien véhicule', 'IND_VEH','gain',     'fixe',        0,    0, 0, null, '—', '03', false, false, 5760],
            ['5900', 'Indemnité eau/électricité', 'IND_EAUEL','gain',      'fixe',        0,    0, 0, null, '—', '02', false, false, 5900],

            // ─── 2xxx : COTISATIONS SALARIALES (groupe 08) ──
            ['2100', 'CNSS part salariale',      'CNSS_S',   'cotisation', 'pourcentage', 2.5, 1500000, 0, null, 'Brut plafonné CNSS', '08', false, false, 2100],
            ['2105', 'CNAMGS part salariale',    'CNAMGS_S', 'cotisation', 'pourcentage', 2.0, 2500000, 0, null, 'Brut plafonné CNAMGS', '08', false, false, 2105],
            ['2210', 'TCS — Taxe complémentaire',  'TCS',    'retenue',    'pourcentage', 5.0,    0, 0, null, 'Net imposable - abattement', '08', false, false, 2210],
            ['2300', 'IRPP',                      'IRPP',   'retenue',    'formule',     0,    0, 0, 'round((net_imposable - 150000) * 0.1)', 'Net imposable - parts fiscales', '08', false, false, 2300],

            // ─── 2xxx : COTISATIONS PATRONALES (préfixe PAT_) ──
            ['2110', 'CNSS part patronale',      'CNSS_P',   'cotisation', 'pourcentage', 16.0, 1500000, 0, null, 'Brut plafonné CNSS', '08', false, false, 2110],
            ['2115', 'CNAMGS part patronale',    'CNAMGS_P', 'cotisation', 'pourcentage', 4.1, 2500000, 0, null, 'Brut plafonné CNAMGS', '08', false, false, 2115],
            ['2600', 'FNH — Fonds national habitat', 'FNH', 'cotisation', 'pourcentage', 2.0,    0, 0, null, 'Brut', '08', false, false, 2600],
            ['2700', 'CFP — Cotisation formation prof.', 'CFP', 'cotisation', 'pourcentage', 0.5, 0, 0, null, 'Brut', '08', false, false, 2700],
        ];

        // Indices : 0=code, 1=libelle, 2=libelle_court, 3=type, 4=base_calcul,
        // 5=taux, 6=valeur2, 7=montant_fixe, 8=formule, 9=base_calcul_libelle,
        // 10=groupe_code, 11=imposable, 12=cotisable, 13=ordre_affichage
        foreach ($rubriques as $r) {
            $code = $r[0];
            $libelle = $r[1];
            // Convention interne PaieCalculator : préfixe PAT_ pour les patronales
            if (str_contains(strtolower($libelle), 'patronale') && !str_starts_with($code, 'PAT_')) {
                $code = 'PAT_' . $code;
            }
            Rubrique::updateOrCreate(
                ['code' => $code],
                [
                    'groupe_rubrique_id'    => $groupesIds[$r[10]] ?? null,
                    'libelle'               => $r[1],
                    'libelle_court'         => $r[2],
                    'type'                  => $r[3],
                    'base_calcul'           => $r[4],
                    'taux'                  => $r[5],
                    'valeur2'               => $r[6],
                    'montant_fixe'          => $r[7],
                    'formule'               => $r[8],
                    'base_calcul_libelle'   => $r[9],
                    'imposable'             => (bool) $r[11],
                    'cotisable'             => (bool) $r[12],
                    'ordre_affichage'       => (int) $r[13],
                    'statut'                => 1,
                ]
            );
        }

        $this->command?->info(sprintf(
            'Catalogue Paie ANPI seedé : %d groupes, %d rubriques',
            count($groupes), count($rubriques)
        ));
    }
}
