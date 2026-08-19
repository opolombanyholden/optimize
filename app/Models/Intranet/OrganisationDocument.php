<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganisationDocument extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_organisation_documents';

    protected $fillable = [
        'organisation_id', 'type_document_id', 'fichier', 'nom_original',
        'taille', 'mime', 'date_expiration', 'notes', 'uploaded_by',
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'taille'          => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(ContactOrganisation::class, 'organisation_id');
    }

    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'type_document_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->fichier ? asset('storage/' . ltrim($this->fichier, '/')) : null;
    }

    public function getEstExpireAttribute(): bool
    {
        return $this->date_expiration && $this->date_expiration->isPast();
    }

    public function getExpireBientotAttribute(): bool
    {
        return $this->date_expiration
            && !$this->est_expire
            && $this->date_expiration->diffInDays(now()) <= 30;
    }

    public function getTailleHumaineAttribute(): string
    {
        $b = $this->taille ?? 0;
        if ($b < 1024) return $b . ' o';
        if ($b < 1048576) return round($b / 1024, 1) . ' Ko';
        return round($b / 1048576, 1) . ' Mo';
    }
}
