<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avancement extends Model
{
    public const STATUT_PROPOSE = 'propose';
    public const STATUT_VALIDE  = 'valide';
    public const STATUT_REFUSE  = 'refuse';

    public const ORIGINE_MANUEL      = 'manuel';
    public const ORIGINE_AUTOMATIQUE = 'automatique';

    protected $fillable = [
        'employee_id', 'grade_id', 'grade_precedent_id',
        'date_effet', 'motif', 'reference_document',
        'decide_par', 'commentaire',
        'statut', 'origine',
        'valide_par', 'valide_at', 'motif_refus',
    ];

    protected function casts(): array
    {
        return [
            'date_effet' => 'date',
            'valide_at'  => 'datetime',
        ];
    }

    public function employee()      { return $this->belongsTo(Employee::class); }
    public function grade()         { return $this->belongsTo(Grade::class); }
    public function gradePrecedent(){ return $this->belongsTo(Grade::class, 'grade_precedent_id'); }
    public function decideur()      { return $this->belongsTo(User::class, 'decide_par'); }
    public function valideur()      { return $this->belongsTo(User::class, 'valide_par'); }

    public function scopeProposes($q) { return $q->where('statut', self::STATUT_PROPOSE); }
    public function scopeValides($q)  { return $q->where('statut', self::STATUT_VALIDE); }
    public function scopeRefuses($q)  { return $q->where('statut', self::STATUT_REFUSE); }
    public function scopeAutomatiques($q) { return $q->where('origine', self::ORIGINE_AUTOMATIQUE); }

    /**
     * À la création :
     *  - renseigner grade_precedent_id depuis le grade actuel de l'employé
     *  - initialiser statut = 'valide' pour les avancements MANUELS uniquement
     *    (les 'automatique' restent en 'propose' jusqu'à validation manuelle)
     *  - synchroniser Employee.grade_id UNIQUEMENT si statut = 'valide'
     */
    protected static function booted(): void
    {
        static::creating(function (self $a) {
            if (empty($a->grade_precedent_id) && $a->employee_id) {
                $emp = Employee::find($a->employee_id);
                if ($emp && $emp->grade_id && $emp->grade_id != $a->grade_id) {
                    $a->grade_precedent_id = $emp->grade_id;
                }
            }
            if (empty($a->origine))  $a->origine  = self::ORIGINE_MANUEL;
            if (empty($a->statut)) {
                $a->statut = $a->origine === self::ORIGINE_AUTOMATIQUE
                    ? self::STATUT_PROPOSE
                    : self::STATUT_VALIDE;
            }
        });

        static::created(function (self $a) {
            if ($a->statut === self::STATUT_VALIDE && $a->employee_id && $a->grade_id) {
                Employee::where('id', $a->employee_id)->update(['grade_id' => $a->grade_id]);
            }
        });
    }
}
