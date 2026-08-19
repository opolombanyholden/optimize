<?php

namespace Database\Seeders;

use App\Models\Referentiel\Departement;
use App\Models\Referentiel\Nationalite;
use App\Models\Referentiel\NiveauQualification;
use App\Models\Referentiel\Poste;
use App\Models\Referentiel\TypeContrat;
use App\Models\TypeEvenementCarriere;
use Illuminate\Database\Seeder;

class ReferentielsRhSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Types de contrat ──────────────────────────────
        $typesContrat = [
            ['code' => 'CDI',         'libelle' => 'CDI - Contrat à durée indéterminée', 'ordre' => 1],
            ['code' => 'CDD',         'libelle' => 'CDD - Contrat à durée déterminée',   'ordre' => 2],
            ['code' => 'STAGE',       'libelle' => 'Stage',                              'ordre' => 3],
            ['code' => 'CONSULTANT',  'libelle' => 'Consultant / Prestataire',           'ordre' => 4],
            ['code' => 'INTERIM',     'libelle' => 'Intérim',                            'ordre' => 5],
            ['code' => 'APPRENTI',    'libelle' => 'Apprentissage',                      'ordre' => 6],
        ];
        foreach ($typesContrat as $t) {
            TypeContrat::updateOrCreate(['code' => $t['code']], array_merge($t, ['statut' => 1]));
        }

        // ─── Postes (catalogue de départ — modulable depuis l'admin) ──
        $postes = [
            ['code' => 'DG',           'libelle' => 'Directeur Général',           'ordre' => 1],
            ['code' => 'DAF',          'libelle' => 'Directeur Administratif et Financier', 'ordre' => 2],
            ['code' => 'DRH',          'libelle' => 'Directeur des Ressources Humaines',    'ordre' => 3],
            ['code' => 'DT',           'libelle' => 'Directeur Technique',          'ordre' => 4],
            ['code' => 'CHEF_PROJ',    'libelle' => 'Chef de projet',               'ordre' => 10],
            ['code' => 'DEV_BACK',     'libelle' => 'Développeur backend',          'ordre' => 11],
            ['code' => 'DEV_FRONT',    'libelle' => 'Développeur frontend',         'ordre' => 12],
            ['code' => 'DEV_FULL',     'libelle' => 'Développeur full-stack',       'ordre' => 13],
            ['code' => 'DESIGNER',     'libelle' => 'Designer / UX',                'ordre' => 14],
            ['code' => 'COMPTABLE',    'libelle' => 'Comptable',                    'ordre' => 20],
            ['code' => 'ASSISTANT',    'libelle' => 'Assistant(e) administratif(ve)', 'ordre' => 21],
            ['code' => 'COMMERCIAL',   'libelle' => 'Commercial',                   'ordre' => 22],
            ['code' => 'SUPPORT',      'libelle' => 'Support client',               'ordre' => 23],
            ['code' => 'STAGIAIRE',    'libelle' => 'Stagiaire',                    'ordre' => 90],
        ];
        foreach ($postes as $p) {
            Poste::updateOrCreate(['code' => $p['code']], array_merge($p, ['statut' => 1]));
        }

        // ─── Départements ──────────────────────────────────
        $departements = [
            ['code' => 'DIR',          'libelle' => 'Direction Générale',           'ordre' => 1],
            ['code' => 'DAF',          'libelle' => 'Administration & Finance',     'ordre' => 2],
            ['code' => 'DRH',          'libelle' => 'Ressources Humaines',          'ordre' => 3],
            ['code' => 'IT',           'libelle' => 'Informatique / Technique',     'ordre' => 4],
            ['code' => 'COMMERCIAL',   'libelle' => 'Commercial / Ventes',          'ordre' => 5],
            ['code' => 'MARKETING',    'libelle' => 'Marketing & Communication',    'ordre' => 6],
            ['code' => 'PROD',         'libelle' => 'Production / Opérations',      'ordre' => 7],
            ['code' => 'SUPPORT',      'libelle' => 'Support / Service client',     'ordre' => 8],
            ['code' => 'JURIDIQUE',    'libelle' => 'Juridique',                    'ordre' => 9],
        ];
        foreach ($departements as $d) {
            Departement::updateOrCreate(['code' => $d['code']], array_merge($d, ['statut' => 1]));
        }

        // ─── Types d'évènement de carrière ─────────────────
        $typesEvenement = [
            ['code' => 'EMBAUCHE',         'libelle' => 'Embauche',                          'ordre' => 1],
            ['code' => 'PROMOTION',        'libelle' => 'Promotion',                         'ordre' => 2],
            ['code' => 'MUTATION',         'libelle' => 'Mutation interne',                  'ordre' => 3],
            ['code' => 'CHANGEMENT_POSTE', 'libelle' => 'Changement de poste',               'ordre' => 4],
            ['code' => 'CHANGEMENT_DEPT',  'libelle' => 'Changement de département',         'ordre' => 5],
            ['code' => 'AUGMENTATION',     'libelle' => 'Augmentation salariale',            'ordre' => 6],
            ['code' => 'RENOUVELLEMENT',   'libelle' => 'Renouvellement de contrat',         'ordre' => 7],
            ['code' => 'TITULARISATION',   'libelle' => 'Titularisation',                    'ordre' => 8],
            ['code' => 'SUSPENSION',       'libelle' => 'Suspension temporaire',             'ordre' => 9],
            ['code' => 'REPRISE',          'libelle' => 'Reprise d\'activité',               'ordre' => 10],
            ['code' => 'DEPART',           'libelle' => 'Départ (toutes causes)',            'ordre' => 11],
            ['code' => 'DISTINCTION',      'libelle' => 'Distinction / Reconnaissance',      'ordre' => 12],
        ];
        foreach ($typesEvenement as $t) {
            TypeEvenementCarriere::updateOrCreate(['code' => $t['code']], array_merge($t, ['statut' => 1]));
        }

        // ─── Niveaux de qualification (système académique + professionnel) ──
        $niveaux = [
            // Pré-universitaire
            ['code' => 'SANS',         'libelle' => 'Sans diplôme',                             'ordre' => 1],
            ['code' => 'CEP',          'libelle' => 'CEP (Certificat d\'études primaires)',     'ordre' => 2],
            ['code' => 'BEPC',         'libelle' => 'BEPC (Brevet d\'études du premier cycle)', 'ordre' => 3],
            ['code' => 'CAP',          'libelle' => 'CAP (Certificat d\'aptitude professionnelle)', 'ordre' => 4],
            ['code' => 'BEP',          'libelle' => 'BEP (Brevet d\'études professionnelles)',  'ordre' => 5],
            ['code' => 'PROBATOIRE',   'libelle' => 'Probatoire',                               'ordre' => 6],
            ['code' => 'BAC',          'libelle' => 'Baccalauréat',                             'ordre' => 7],
            ['code' => 'BAC_PRO',      'libelle' => 'Baccalauréat professionnel',               'ordre' => 8],
            ['code' => 'BAC_TECHNO',   'libelle' => 'Baccalauréat technologique',               'ordre' => 9],
            // Universitaire — cycle court
            ['code' => 'BAC_1',        'libelle' => 'Bac+1',                                    'ordre' => 10],
            ['code' => 'BTS',          'libelle' => 'BTS (Brevet de technicien supérieur)',     'ordre' => 11],
            ['code' => 'DUT',          'libelle' => 'DUT (Diplôme universitaire de technologie)', 'ordre' => 12],
            ['code' => 'DEUG',         'libelle' => 'DEUG / Bac+2',                             'ordre' => 13],
            ['code' => 'LICENCE',      'libelle' => 'Licence / Bac+3',                          'ordre' => 14],
            ['code' => 'LICENCE_PRO',  'libelle' => 'Licence professionnelle',                  'ordre' => 15],
            ['code' => 'BACHELOR',     'libelle' => 'Bachelor',                                 'ordre' => 16],
            // Cycle long
            ['code' => 'MAITRISE',     'libelle' => 'Maîtrise / Bac+4',                         'ordre' => 17],
            ['code' => 'MASTER_1',     'libelle' => 'Master 1',                                 'ordre' => 18],
            ['code' => 'MASTER_2',     'libelle' => 'Master 2 / Bac+5',                         'ordre' => 19],
            ['code' => 'INGENIEUR',    'libelle' => 'Diplôme d\'ingénieur',                     'ordre' => 20],
            ['code' => 'MBA',          'libelle' => 'MBA (Master of Business Administration)',  'ordre' => 21],
            ['code' => 'DOCTORAT',     'libelle' => 'Doctorat / PhD / Bac+8',                   'ordre' => 22],
            // Pro / certifications
            ['code' => 'CERTIF_PRO',   'libelle' => 'Certification professionnelle',            'ordre' => 30],
            ['code' => 'CERTIF_TECH',  'libelle' => 'Certification technique (éditeur)',        'ordre' => 31],
            ['code' => 'AUTRE',        'libelle' => 'Autre',                                    'ordre' => 99],
        ];
        foreach ($niveaux as $n) {
            NiveauQualification::updateOrCreate(['code' => $n['code']], array_merge($n, ['statut' => 1]));
        }

        // ─── Nationalités (Afrique + grandes nations partenaires) ──
        $nationalites = [
            // Afrique centrale (zone d'usage prioritaire)
            ['code' => 'GAB', 'libelle' => 'Gabonaise',                'ordre' => 1],
            ['code' => 'CMR', 'libelle' => 'Camerounaise',             'ordre' => 2],
            ['code' => 'COG', 'libelle' => 'Congolaise (Brazzaville)', 'ordre' => 3],
            ['code' => 'COD', 'libelle' => 'Congolaise (RDC)',         'ordre' => 4],
            ['code' => 'TCD', 'libelle' => 'Tchadienne',               'ordre' => 5],
            ['code' => 'CAF', 'libelle' => 'Centrafricaine',           'ordre' => 6],
            ['code' => 'GNQ', 'libelle' => 'Equato-guinéenne',         'ordre' => 7],
            ['code' => 'STP', 'libelle' => 'Sao-toméenne',             'ordre' => 8],
            // Afrique de l'Ouest
            ['code' => 'SEN', 'libelle' => 'Sénégalaise',              'ordre' => 10],
            ['code' => 'CIV', 'libelle' => 'Ivoirienne',               'ordre' => 11],
            ['code' => 'BEN', 'libelle' => 'Béninoise',                'ordre' => 12],
            ['code' => 'TGO', 'libelle' => 'Togolaise',                'ordre' => 13],
            ['code' => 'BFA', 'libelle' => 'Burkinabé',                'ordre' => 14],
            ['code' => 'MLI', 'libelle' => 'Malienne',                 'ordre' => 15],
            ['code' => 'NER', 'libelle' => 'Nigérienne',               'ordre' => 16],
            ['code' => 'NGA', 'libelle' => 'Nigériane',                'ordre' => 17],
            ['code' => 'GHA', 'libelle' => 'Ghanéenne',                'ordre' => 18],
            ['code' => 'GIN', 'libelle' => 'Guinéenne',                'ordre' => 19],
            ['code' => 'MRT', 'libelle' => 'Mauritanienne',            'ordre' => 20],
            // Afrique de l'Est et australe
            ['code' => 'KEN', 'libelle' => 'Kényane',                  'ordre' => 25],
            ['code' => 'ETH', 'libelle' => 'Ethiopienne',              'ordre' => 26],
            ['code' => 'TZA', 'libelle' => 'Tanzanienne',              'ordre' => 27],
            ['code' => 'UGA', 'libelle' => 'Ougandaise',               'ordre' => 28],
            ['code' => 'RWA', 'libelle' => 'Rwandaise',                'ordre' => 29],
            ['code' => 'BDI', 'libelle' => 'Burundaise',               'ordre' => 30],
            ['code' => 'AGO', 'libelle' => 'Angolaise',                'ordre' => 31],
            ['code' => 'MOZ', 'libelle' => 'Mozambicaine',             'ordre' => 32],
            ['code' => 'ZAF', 'libelle' => 'Sud-africaine',            'ordre' => 33],
            // Afrique du Nord
            ['code' => 'DZA', 'libelle' => 'Algérienne',               'ordre' => 40],
            ['code' => 'MAR', 'libelle' => 'Marocaine',                'ordre' => 41],
            ['code' => 'TUN', 'libelle' => 'Tunisienne',               'ordre' => 42],
            ['code' => 'EGY', 'libelle' => 'Egyptienne',               'ordre' => 43],
            ['code' => 'LBY', 'libelle' => 'Libyenne',                 'ordre' => 44],
            // Europe
            ['code' => 'FRA', 'libelle' => 'Française',                'ordre' => 50],
            ['code' => 'BEL', 'libelle' => 'Belge',                    'ordre' => 51],
            ['code' => 'CHE', 'libelle' => 'Suisse',                   'ordre' => 52],
            ['code' => 'GBR', 'libelle' => 'Britannique',              'ordre' => 53],
            ['code' => 'DEU', 'libelle' => 'Allemande',                'ordre' => 54],
            ['code' => 'ESP', 'libelle' => 'Espagnole',                'ordre' => 55],
            ['code' => 'ITA', 'libelle' => 'Italienne',                'ordre' => 56],
            ['code' => 'PRT', 'libelle' => 'Portugaise',               'ordre' => 57],
            ['code' => 'NLD', 'libelle' => 'Néerlandaise',             'ordre' => 58],
            // Amériques
            ['code' => 'USA', 'libelle' => 'Américaine',               'ordre' => 60],
            ['code' => 'CAN', 'libelle' => 'Canadienne',               'ordre' => 61],
            ['code' => 'BRA', 'libelle' => 'Brésilienne',              'ordre' => 62],
            // Asie
            ['code' => 'CHN', 'libelle' => 'Chinoise',                 'ordre' => 70],
            ['code' => 'IND', 'libelle' => 'Indienne',                 'ordre' => 71],
            ['code' => 'JPN', 'libelle' => 'Japonaise',                'ordre' => 72],
            ['code' => 'LBN', 'libelle' => 'Libanaise',                'ordre' => 73],
            ['code' => 'SYR', 'libelle' => 'Syrienne',                 'ordre' => 74],
            ['code' => 'TUR', 'libelle' => 'Turque',                   'ordre' => 75],
            // Autre
            ['code' => 'AUTRE',     'libelle' => 'Autre',              'ordre' => 99],
            ['code' => 'APATRIDE',  'libelle' => 'Apatride',           'ordre' => 100],
        ];
        foreach ($nationalites as $n) {
            Nationalite::updateOrCreate(['code' => $n['code']], array_merge($n, ['statut' => 1]));
        }

        $this->command?->info(sprintf(
            'Référentiels RH seedés : %d types de contrat, %d postes, %d départements, %d types d\'évènement, %d niveaux qualif., %d nationalités.',
            count($typesContrat), count($postes), count($departements), count($typesEvenement), count($niveaux), count($nationalites)
        ));
    }
}
