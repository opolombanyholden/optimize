<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class OrdreModeleChamp extends Model
{
    protected $table = 'finance_ordre_modele_champs';

    protected $fillable = [
        'modele_id', 'code_champ', 'label_personnalise',
        'type_saisie', 'options_json', 'mapping_gl',
        'obligatoire', 'largeur_col', 'ordre',
        'placeholder', 'valeur_par_defaut',
    ];

    protected $casts = [
        'options_json' => 'array',
        'obligatoire'  => 'boolean',
        'largeur_col'  => 'integer',
        'ordre'        => 'integer',
    ];

    public const TYPES_SAISIE = [
        'text'           => 'Texte court',
        'number'         => 'Nombre',
        'date'           => 'Date',
        'textarea'       => 'Texte long',
        'select'         => 'Liste déroulante',
        'tiers'          => 'Tiers (client/fournisseur)',
        'ligne_budgetaire' => 'Ligne budgétaire',
    ];

    /**
     * Colonnes de `grand_livres` qu'il est pertinent d'exposer comme mapping.
     * Sert au sélecteur dans l'admin des modèles.
     */
    public const CHAMPS_GL_DISPONIBLES = [
        'date_ecriture'      => 'Date d\'écriture',
        'dateeffect'         => 'Date d\'effet',
        'imputation'         => 'Imputation budgétaire',
        'nature'             => 'Nature',
        'libelle'            => 'Libellé',
        'description'        => 'Description',
        'beneficiaire'       => 'Bénéficiaire',
        'rib'                => 'RIB',
        'banque'             => 'Banque',
        'mode_reglement'     => 'Mode de règlement',
        'num_piece'          => 'Numéro de pièce',
        'ref_piece'          => 'Référence pièce',
        'nature_piece'       => 'Nature pièce',
        'libelle_piece'      => 'Libellé pièce',
        'nif'                => 'NIF',
        'compte_general'     => 'Compte général',
        'compte_auxiliaire'  => 'Compte auxiliaire',
        'montant_tc'         => 'Montant (toutes charges)',
    ];

    public function modele()
    {
        return $this->belongsTo(OrdreModele::class, 'modele_id');
    }
}
