<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeDocument extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_types_documents';

    protected $fillable = [
        'code', 'libelle', 'description', 'icone', 'ordre',
        'avec_expiration', 'actif',
    ];

    protected $casts = [
        'avec_expiration' => 'boolean',
        'actif'           => 'boolean',
        'ordre'           => 'integer',
    ];

    public function exigences()
    {
        return $this->hasMany(TypeDocumentExigence::class, 'type_document_id');
    }

    public function documentsFournis()
    {
        return $this->hasMany(OrganisationDocument::class, 'type_document_id');
    }

    public function estObligatoirePour(string $typeOrganisation): bool
    {
        return $this->exigences
            ->firstWhere('type_organisation', $typeOrganisation)
            ?->obligatoire ?? false;
    }

    public function estRequisPour(string $typeOrganisation): bool
    {
        return $this->exigences->contains('type_organisation', $typeOrganisation);
    }

    public function scopeActif($q) { return $q->where('actif', true); }
}
