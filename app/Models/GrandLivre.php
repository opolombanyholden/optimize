<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrandLivre extends Model
{
    protected $fillable = [
        'date_ecriture', 'id_entite', 'dateeffect', 'montant_tc',
        'sens', 'mode_reglement', 'exercice', 'id_exercicebudgetaire',
        'description', 'id_user', 'rib', 'banque',
        'beneficiaire', 'type_beneficiaire', 'beneficiaire_interne',
        'id_beneficiaire', 'id_beneficiaire_interne',
        'libelle_piece', 'type_piece', 'id_ligne', 'id_titre',
        'imputation', 'nature', 'num_piece', 'ref_piece', 'nature_piece',
        'montant_signe_tc', 'compte_id', 'compte_general',
        'compte_auxiliaire', 'role_tiers', 'journal', 'libelle',
        'devise', 'montant_tr', 'code_lettrage', 'type_marquage',
        'date_lettrage', 'date_pointage', 'lettre_rappro', 'date_rappro',
        'type_ecriture', 'num_lot', 'num_ecriture', 'code_tiers',
        'montant_signe_tr', 'id_famillecodeanalytique', 'id_codeanalytique',
        'id_organisation', 'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'effacer', 'num_stat', 'nif', 'id_facture',
    ];

    protected function casts(): array
    {
        return [
            'date_ecriture' => 'date',
            'dateeffect' => 'datetime',
            'date_rappro' => 'date',
        ];
    }

    public function entite()
    {
        return $this->belongsTo(Entite::class, 'id_entite');
    }

    public function exerciceBudgetaire()
    {
        return $this->belongsTo(Exercice::class, 'id_exercicebudgetaire');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id');
    }

    public function details()
    {
        return $this->hasMany(GrandLivreDetail::class, 'id_facture', 'id_facture');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'id_organisation');
    }
}
