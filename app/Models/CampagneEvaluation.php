<?php

namespace App\Models;

use App\Models\Intranet\ContactOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampagneEvaluation extends Model
{
    use SoftDeletes;

    public const STATUT_BROUILLON = 0;
    public const STATUT_EN_COURS = 1;
    public const STATUT_CLOTUREE = 2;

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_CLOTUREE => 'Clôturée',
    ];

    protected $table = 'campagnes_evaluation';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'statut' => 'integer',
        ];
    }

    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }
    public function prestataires() { return $this->belongsToMany(ContactOrganisation::class, 'campagne_prestataires', 'campagne_id', 'prestataire_id')->withPivot('evaluation_faite')->withTimestamps(); }
    public function evaluations() { return $this->hasMany(EvaluationPrestataire::class, 'campagne_id'); }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
}
