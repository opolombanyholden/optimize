<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'libelle', 'description', 'ordre', 'actif',
        'avancement_automatique', 'duree_max_mois', 'grade_suivant_id',
    ];

    protected function casts(): array
    {
        return [
            'actif'                  => 'boolean',
            'ordre'                  => 'integer',
            'avancement_automatique' => 'boolean',
            'duree_max_mois'         => 'integer',
        ];
    }

    public function criteres()      { return $this->hasMany(GradeCritere::class)->orderBy('ordre'); }
    public function employees()     { return $this->hasMany(Employee::class); }
    public function avancements()   { return $this->hasMany(Avancement::class); }
    public function gradeSuivant()  { return $this->belongsTo(Grade::class, 'grade_suivant_id'); }

    public function scopeActifs($q)      { return $q->where('actif', true); }
    public function scopeOrdonnes($q)    { return $q->orderBy('ordre')->orderBy('libelle'); }
    public function scopeAutomatiques($q){ return $q->where('avancement_automatique', true)->whereNotNull('duree_max_mois'); }

    /**
     * Grade cible pour un avancement automatique.
     *  1) grade_suivant_id explicite si défini
     *  2) sinon, prochain grade par ordre (parmi actifs)
     */
    public function grade_cible_auto(): ?Grade
    {
        if ($this->grade_suivant_id) return $this->gradeSuivant;
        return static::actifs()->where('ordre', '>', $this->ordre)->ordonnes()->first();
    }
}
