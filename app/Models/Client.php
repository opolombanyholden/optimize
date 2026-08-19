<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'code', 'raison_sociale', 'forme_juridique', 'nif', 'rccm',
        'adresse', 'ville', 'pays', 'telephone', 'email', 'site_web',
        'contact_nom', 'contact_telephone', 'contact_email',
        'rib', 'banque', 'notes', 'statut',
        'created_by', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'raison_sociale', 'email', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('client')
            ->setDescriptionForEvent(fn(string $event) => "Client {$event}");
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relations manuelles : la colonne tiers_type stocke 'client'/'fournisseur', pas le FQCN
    public function factures()
    {
        return $this->hasMany(Facture::class, 'tiers_id')->where('tiers_type', 'client');
    }

    public function operations()
    {
        return $this->hasMany(OperationFinanciere::class, 'tiers_id')->where('tiers_type', 'client');
    }

    public function scopeActif($q) { return $q->where('statut', 1); }
}
