<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sanction extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['employee_id', 'type', 'motif', 'date_notification', 'date_fin', 'niveau_gravite', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('sanction')
            ->setDescriptionForEvent(fn(string $event) => "Sanction {$event}");
    }

    protected $fillable = [
        'employee_id', 'type', 'motif', 'description',
        'date_fait', 'date_notification', 'date_fin',
        'decidee_par', 'reaction_employe', 'niveau_gravite',
        'fichiersjoin', 'extra_attributes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_fait'         => 'date',
            'date_notification' => 'date',
            'date_fin'          => 'date',
            'fichiersjoin'      => 'array',
            'extra_attributes'  => 'array',
        ];
    }

    public const TYPES = [
        'rappel_ordre'   => 'Rappel à l\'ordre',
        'blame'          => 'Blâme',
        'avertissement'  => 'Avertissement',
        'mise_a_pied'    => 'Mise à pied',
        'licenciement'   => 'Licenciement disciplinaire',
    ];

    public const NIVEAUX = [
        'mineure' => 'Mineure',
        'modere'  => 'Modérée',
        'grave'   => 'Grave',
        'severe'  => 'Sévère',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function decideur()
    {
        return $this->belongsTo(User::class, 'decidee_par');
    }

    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
