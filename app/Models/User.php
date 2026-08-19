<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'prenoms',
        'email',
        'password',
        'contact',
        'email_interne',
        'apiclient',
        'matricule',
        'statut',
        'profile_photo_path',
        'current_team_id',
        'extra_attributes',
        'poste',
        'date_naissance',
        'date_embauche',
        'bureau',
        'bio',
        'linkedin',
        'must_change_password',
        'password_changed_at',
        'password_reset_at',
        'password_reset_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'extra_attributes'     => 'array',
            'date_naissance'       => 'date',
            'date_embauche'        => 'date',
            'must_change_password' => 'boolean',
            'password_changed_at'  => 'datetime',
            'password_reset_at'    => 'datetime',
        ];
    }

    // Relations

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function exercices()
    {
        return $this->hasMany(Exercice::class, 'id_user');
    }

    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'chefs');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'id_user');
    }

    // ── Intranet ─────────────────────────────────────────────
    public function groupesIntranet()
    {
        return $this->belongsToMany(
            \App\Models\Intranet\Groupe::class,
            'intranet_groupe_user'
        )->withPivot('role')->withTimestamps();
    }

    public function likesIntranet()
    {
        return $this->hasMany(\App\Models\Intranet\Like::class, 'user_id');
    }

    public function commentairesIntranet()
    {
        return $this->hasMany(\App\Models\Intranet\Commentaire::class, 'user_id');
    }

    public function services()
    {
        return $this->belongsToMany(
            \App\Models\Intranet\Service::class,
            'intranet_service_user',
            'user_id',
            'service_id'
        )->withPivot(['poste', 'est_principal', 'date_arrivee', 'date_depart'])
         ->withTimestamps();
    }

    public function servicePrincipal()
    {
        return $this->services()->wherePivot('est_principal', true)->first();
    }

    // Scopes

    public function scopeActif($query)
    {
        return $query->where('statut', 1);
    }

    // Accessors

    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->prenoms}";
    }
}
