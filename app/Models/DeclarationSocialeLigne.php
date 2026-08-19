<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeclarationSocialeLigne extends Model
{
    protected $table = 'declaration_sociale_lignes';

    protected $fillable = [
        'declaration_sociale_id', 'employee_id',
        'matricule_employeur', 'matricule_organisme', 'nip',
        'noms', 'prenoms', 'date_naissance', 'sexe',
        'nb_jours_travailles', 'brut', 'brut_plafonne',
        'cot_salariale', 'cot_patronale', 'bulletins_inclus',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance'    => 'date',
            'brut'              => 'decimal:2',
            'brut_plafonne'     => 'decimal:2',
            'cot_salariale'     => 'decimal:2',
            'cot_patronale'     => 'decimal:2',
            'bulletins_inclus'  => 'array',
        ];
    }

    public function declaration() { return $this->belongsTo(DeclarationSociale::class, 'declaration_sociale_id'); }
    public function employee()    { return $this->belongsTo(Employee::class); }
}
