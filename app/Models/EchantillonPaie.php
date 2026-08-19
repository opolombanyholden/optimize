<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EchantillonPaie extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'echantillons_paie';

    protected $fillable = [
        'code', 'libelle', 'description',
        'couleur', 'icone', 'statut',
        'created_by', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'statut'           => 'boolean',
            'extra_attributes' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'libelle', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('echantillon_paie')
            ->setDescriptionForEvent(fn(string $event) => "Échantillon de paie {$event}");
    }

    public function employes()
    {
        return $this->belongsToMany(Employee::class, 'echantillon_paie_employe', 'echantillon_paie_id', 'employee_id')
            ->withPivot('notes')
            ->withTimestamps();
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActif($q)
    {
        return $q->where('statut', true);
    }
}
