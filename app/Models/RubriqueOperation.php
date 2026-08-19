<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RubriqueOperation extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'rubriques_operations';

    protected $fillable = [
        'code', 'libelle', 'description', 'ligne_id',
        'sens', 'categorie', 'ordre_affichage', 'statut',
        'created_by',
    ];

    public const SENS = [
        'depense' => 'Dépense',
        'recette' => 'Recette',
        'mixte'   => 'Mixte (dép./rec.)',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'libelle', 'ligne_id', 'sens', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('rubrique_operation')
            ->setDescriptionForEvent(fn(string $event) => "Rubrique d'opération {$event}");
    }

    public function ligne()  { return $this->belongsTo(Ligne::class, 'ligne_id'); }
    public function createur(){ return $this->belongsTo(User::class, 'created_by'); }
    public function details(){ return $this->hasMany(OperationFinanciereDetail::class, 'rubrique_id'); }

    public function getSensLibelleAttribute(): string
    {
        return self::SENS[$this->sens] ?? $this->sens;
    }

    public function scopeActif($q)   { return $q->where('statut', 1); }
    public function scopeDepense($q) { return $q->whereIn('sens', ['depense', 'mixte']); }
    public function scopeRecette($q) { return $q->whereIn('sens', ['recette', 'mixte']); }
}
