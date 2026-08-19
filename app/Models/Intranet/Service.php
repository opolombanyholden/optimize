<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_services';

    protected $fillable = [
        'nom', 'description', 'chef_du_service_id',
        'code', 'couleur', 'icone', 'type_entite',
        'email', 'telephone', 'localisation',
        'parent_id', 'est_actif',
    ];

    public const TYPES_ENTITE = [
        'direction'   => 'Direction',
        'service'     => 'Service',
        'departement' => 'Département',
        'unite'       => 'Unité',
        'equipe'      => 'Équipe',
        'autre'       => 'Autre',
    ];

    public function getTypeEntiteLibelleAttribute(): string
    {
        return self::TYPES_ENTITE[$this->type_entite] ?? 'Service';
    }

    protected $casts = [
        'est_actif' => 'boolean',
    ];

    // ── Relations ────────────────────────────────────────

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_du_service_id');
    }

    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function sousServices()
    {
        return $this->hasMany(Service::class, 'parent_id');
    }

    public function membres()
    {
        return $this->belongsToMany(User::class, 'intranet_service_user', 'service_id', 'user_id')
                    ->withPivot(['poste', 'est_principal', 'date_arrivee', 'date_depart'])
                    ->withTimestamps();
    }

    public function membresActifs()
    {
        return $this->membres()->wherePivotNull('date_depart');
    }

    // ── Accessors ────────────────────────────────────────

    public function getCouleurAffichageAttribute(): string
    {
        return $this->couleur ?? '#0D9488';
    }

    public function getIconeAffichageAttribute(): string
    {
        return $this->icone ?? 'fa-building';
    }

    public function getEffectifAttribute(): int
    {
        return $this->membresActifs()->count();
    }

    // ── Scopes ───────────────────────────────────────────

    public function scopeActif($q)
    {
        return $q->where('est_actif', true);
    }

    public function scopeRacine($q)
    {
        return $q->whereNull('parent_id');
    }
}
