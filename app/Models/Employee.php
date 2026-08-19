<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'noms', 'prenoms', 'matricule', 'email', 'contact',
                'poste', 'departement', 'type_contrat',
                'salaire_base', 'iban', 'numero_secu', 'nip',
                'date_embauche', 'date_fin_contrat', 'statut',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('employee')
            ->setDescriptionForEvent(fn(string $event) => "Employé {$event}");
    }

    protected $fillable = [
        'user_id', 'noms', 'prenoms', 'matricule',
        'date_naissance', 'lieu_naissance', 'nationalite',
        'sexe', 'situation_matrimoniale', 'nombre_enfants',
        'email', 'contact', 'adresse', 'id_quartier', 'id_ville', 'id_pays',
        // Localisation administrative
        'pays', 'province', 'departement_geo', 'prefecture', 'sous_prefecture',
        'zone_type', 'commune', 'arrondissement', 'quartier_loc',
        'canton', 'regroupement_village', 'village',
        'date_embauche', 'type_contrat', 'date_fin_contrat',
        'poste', 'departement', 'superieur_hierarchique', 'superieur_poste_id',
        'salaire_base', 'iban', 'numero_secu', 'nip',
        'matricule_cnss', 'matricule_cnamgs', 'parts_fiscales',
        'statut',
        'fichiersjoin', 'extra_attributes',
        'pointage_token',
        'pointage_pin', 'pointage_pin_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance'           => 'date',
            'date_embauche'            => 'date',
            'date_fin_contrat'         => 'date',
            'extra_attributes'         => 'array',
            'pointage_pin'             => 'hashed', // bcrypt automatique
            'pointage_pin_changed_at'  => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Lien hiérarchique par POSTE (référentiel) — privilégier celui-ci.
     * Suit le poste, pas la personne : si le titulaire change, le lien reste valide.
     */
    public function superieurPoste()
    {
        return $this->belongsTo(\App\Models\Referentiel\Poste::class, 'superieur_poste_id');
    }

    /**
     * Override manuel par PERSONNE (legacy / cas particuliers).
     * Utile uniquement si le lien n'est pas inférable depuis le poste.
     */
    public function superieur()
    {
        return $this->belongsTo(Employee::class, 'superieur_hierarchique');
    }

    public function subordonnes()
    {
        return $this->hasMany(Employee::class, 'superieur_hierarchique');
    }

    /**
     * Résout le supérieur ACTUEL (employé occupant le poste désigné).
     * Priorité : poste référencé → fallback override personne legacy.
     */
    public function superieurActuel(): ?Employee
    {
        if ($this->superieur_poste_id) {
            $libelle = $this->superieurPoste?->libelle;
            if ($libelle) {
                return self::where('poste', $libelle)
                    ->where('statut', 1)
                    ->where('id', '!=', $this->id)
                    ->orderBy('date_embauche')
                    ->first();
            }
        }
        return $this->superieur;
    }

    /**
     * Subordonnés actuels : tous les employés actifs dont le superieur_poste_id
     * pointe vers le poste actuellement occupé par cet employé.
     */
    public function subordonnesActuels()
    {
        if (!$this->poste) return collect();
        $posteId = \App\Models\Referentiel\Poste::where('libelle', $this->poste)->value('id');
        if (!$posteId) return collect();
        return self::where('superieur_poste_id', $posteId)
            ->where('statut', 1)
            ->where('id', '!=', $this->id)
            ->get();
    }

    public function affilies()
    {
        return $this->hasMany(Affilie::class, 'employee_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'employee_id');
    }

    public function competences()
    {
        return $this->hasMany(Competence::class, 'employee_id');
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class, 'employee_id');
    }

    public function formations()
    {
        return $this->hasMany(Formation::class, 'employee_id');
    }

    public function paies()
    {
        return $this->hasMany(Paie::class, 'employee_id');
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'employee_id');
    }

    public function evenementsCarriere()
    {
        return $this->hasMany(EvenementCarriere::class, 'employee_id');
    }

    public function scopeActif($query) { return $query->where('statut', 1); }
    public function scopeInactif($query) { return $query->where('statut', 2); }

    public function getNomCompletAttribute(): string
    {
        return "{$this->noms} {$this->prenoms}";
    }
}
