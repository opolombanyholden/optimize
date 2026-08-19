<?php

namespace Database\Seeders;

use App\Models\Finance\OrdreModele;
use App\Models\Finance\OrdreModeleChamp;
use Illuminate\Database\Seeder;

/**
 * Modèles par défaut basés sur les documents ANPI-Gabon :
 *  - Ordre de recette (page 1 du PDF de conception)
 *  - Ordonnance de paiement (page 2 du PDF de conception)
 *
 * Ces modèles peuvent être personnalisés dans l'admin. Le seeder est
 * idempotent : les modèles existants ne sont pas dupliqués mais leurs
 * champs sont recréés (permet de réaligner sur le référentiel).
 */
class OrdreModelesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedOrdreRecette();
        $this->seedOrdonnancePaiement();

        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->command?->info('Modèles d\'ordre seedés : ordre_recette, ordonnance_paiement.');
        }
    }

    private function seedOrdreRecette(): void
    {
        $mod = OrdreModele::updateOrCreate(
            ['code' => 'ordre_recette'],
            [
                'libelle'             => 'Ordre de recette',
                'sens'                => OrdreModele::SENS_RECETTE,
                'numerotation_format' => '{n:03d}',
                'entete_titre'        => 'ORDRE DE RECETTE',
                'entete_soustitre'    => 'AGENCE NATIONALE DE PROMOTION DES INVESTISSEMENTS DU GABON',
                'phrase_intro'        => "L'Agent Comptable est invité à prendre en charge le présent ordre de recette.",
                'phrase_conclusion'   => 'ARRETE LE PRESENT ORDRE DE RECETTE A LA SOMME DE :',
                'avec_mode_reglement' => false,
                'avec_pieces_justif'  => false,
                'actif'               => true,
            ]
        );

        $mod->champs()->delete();
        $champs = [
            ['code_champ' => 'nature_recette',       'label_personnalise' => 'NATURE DE LA RECETTE',   'type_saisie' => 'text',    'mapping_gl' => 'nature',        'obligatoire' => true,  'largeur_col' => 12, 'ordre' => 10, 'placeholder' => 'Ex : Produits divers'],
            ['code_champ' => 'imputation_budget',    'label_personnalise' => 'IMPUTATION BUDGETAIRE',  'type_saisie' => 'text',    'mapping_gl' => 'imputation',    'obligatoire' => true,  'largeur_col' => 3,  'ordre' => 20, 'placeholder' => '7546'],
            ['code_champ' => 'date_emission',        'label_personnalise' => 'DATE D\'EMISSION',       'type_saisie' => 'date',    'mapping_gl' => 'date_ecriture', 'obligatoire' => true,  'largeur_col' => 3,  'ordre' => 30],
            ['code_champ' => 'nature_piece',         'label_personnalise' => 'NATURE DE LA PIECE',     'type_saisie' => 'text',    'mapping_gl' => 'nature_piece',  'obligatoire' => false, 'largeur_col' => 3,  'ordre' => 40],
            ['code_champ' => 'montant',              'label_personnalise' => 'MONTANT',                'type_saisie' => 'number',  'mapping_gl' => 'montant_tc',    'obligatoire' => true,  'largeur_col' => 3,  'ordre' => 50, 'placeholder' => '0'],
        ];
        foreach ($champs as $c) $mod->champs()->create($c);

        $mod->signataires()->delete();
        foreach ([
            ['role_libelle' => 'P. LE DIRECTEUR FINANCIER ET MOYENS GENERAUX', 'ordre' => 10],
            ['role_libelle' => "L'AGENT COMPTABLE",                             'ordre' => 20],
            ['role_libelle' => 'LE DIRECTEUR GENERAL',                          'ordre' => 30],
        ] as $s) $mod->signataires()->create($s);
    }

    private function seedOrdonnancePaiement(): void
    {
        $mod = OrdreModele::updateOrCreate(
            ['code' => 'ordonnance_paiement'],
            [
                'libelle'             => 'Ordonnance de paiement',
                'sens'                => OrdreModele::SENS_DEPENSE,
                'numerotation_format' => '{n:04d}/BF/PR/ANPI-GABON/DG/DFMG/KAE',
                'entete_titre'        => 'ORDONNANCE DE PAIEMENT',
                'entete_soustitre'    => 'AGENCE NATIONALE DE PROMOTION DES INVESTISSEMENTS DU GABON',
                'phrase_intro'        => "La présente ordonnance est assignée payable à la Caisse de l'Agent Comptable de l'Agence Nationale de Promotion des Investissements du Gabon.",
                'phrase_conclusion'   => 'ARRETE LA PRESENTE ORDONNANCE A LA SOMME DE :',
                'avec_mode_reglement' => true,
                'avec_pieces_justif'  => true,
                'actif'               => true,
            ]
        );

        $mod->champs()->delete();
        $champs = [
            ['code_champ' => 'nature_depense',       'label_personnalise' => 'NATURE DE LA DEPENSE',    'type_saisie' => 'text',   'mapping_gl' => 'nature',       'obligatoire' => true,  'largeur_col' => 12, 'ordre' => 10, 'placeholder' => 'Ex : Autres dépenses et achats'],
            ['code_champ' => 'imputation_budget',    'label_personnalise' => 'IMPUTATION BUDGETAIRE',   'type_saisie' => 'text',   'mapping_gl' => 'imputation',   'obligatoire' => true,  'largeur_col' => 3,  'ordre' => 20, 'placeholder' => '618909'],
            ['code_champ' => 'dotation_initiale',    'label_personnalise' => 'DOTATION INITIALE',       'type_saisie' => 'number', 'mapping_gl' => null,           'obligatoire' => false, 'largeur_col' => 3,  'ordre' => 30, 'placeholder' => '0'],
            ['code_champ' => 'solde_precedent',      'label_personnalise' => 'SOLDE PRECEDENT',         'type_saisie' => 'number', 'mapping_gl' => null,           'obligatoire' => false, 'largeur_col' => 3,  'ordre' => 40, 'placeholder' => '0'],
            ['code_champ' => 'nouveau_solde',        'label_personnalise' => 'NOUVEAU SOLDE',           'type_saisie' => 'number', 'mapping_gl' => null,           'obligatoire' => false, 'largeur_col' => 3,  'ordre' => 50, 'placeholder' => '0'],
            // Note : Raison sociale + Adresse retirées ici — désormais gérées par le bloc
            // "Bénéficiaire" du formulaire (source structurée : user / contact / organisation / externe).
            ['code_champ' => 'ref_date',             'label_personnalise' => 'DATE',                    'type_saisie' => 'date',   'mapping_gl' => 'date_ecriture','obligatoire' => true,  'largeur_col' => 3,  'ordre' => 80],
            ['code_champ' => 'pieces_justif',        'label_personnalise' => 'PIECES JUSTIFICATIVES',   'type_saisie' => 'textarea','mapping_gl' => 'ref_piece',   'obligatoire' => false, 'largeur_col' => 6,  'ordre' => 90],
            ['code_champ' => 'montant',              'label_personnalise' => 'MONTANT EN CHIFFRES',     'type_saisie' => 'number', 'mapping_gl' => 'montant_tc',   'obligatoire' => true,  'largeur_col' => 3,  'ordre' => 100, 'placeholder' => '0'],
            ['code_champ' => 'mode_reglement',       'label_personnalise' => 'MODE DE REGLEMENT',       'type_saisie' => 'select', 'mapping_gl' => 'mode_reglement', 'obligatoire' => true, 'largeur_col' => 12, 'ordre' => 110,
             'options_json' => ['numeraire' => 'Numéraire', 'cheque' => 'Chèque', 'virement' => 'Virement bancaire']],
        ];
        foreach ($champs as $c) $mod->champs()->create($c);

        $mod->signataires()->delete();
        foreach ([
            ['role_libelle' => 'LE CONTROLEUR BUDGETAIRE', 'ordre' => 10],
            ['role_libelle' => "L'AGENT COMPTABLE",         'ordre' => 20],
            ['role_libelle' => 'LE DIRECTEUR GENERAL',      'ordre' => 30],
        ] as $s) $mod->signataires()->create($s);
    }
}
