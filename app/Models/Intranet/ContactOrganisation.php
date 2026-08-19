<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ContactOrganisation extends Model
{
    use SoftDeletes, HasPiecesJointes, HasVues, HasPublication, HasLikes, HasCommentaires;

    protected $table = 'intranet_contact_organisations';

    protected $fillable = [
        'nom', 'slug', 'description', 'secteur_id',
        'email', 'telephone', 'adresse', 'site_web', 'logo',
        'media_principal', 'media_principal_type',
        'pays_id', 'ville', 'code_postal',
        'taille', 'chiffre_affaires', 'effectif',
        'siret', 'numero_tva', 'linkedin',
        'tags', 'etiquette',
        'est_client', 'est_prospect', 'vues_count',
        'notes', 'created_by',
        // Fusion Client/Fournisseur/… : type + champs B2B (2026-07-09)
        'type', 'code', 'raison_sociale', 'forme_juridique', 'nif', 'rccm',
        'rib', 'banque', 'statut', 'statut_relation',
        'contact_principal_nom', 'contact_principal_telephone', 'contact_principal_email',
        'extra_attributes',
    ];

    protected $casts = [
        'tags'             => 'array',
        'est_client'       => 'boolean',
        'est_prospect'     => 'boolean',
        'chiffre_affaires' => 'decimal:2',
        'effectif'         => 'integer',
        'vues_count'       => 'integer',
        'extra_attributes' => 'array',
    ];

    // ── Types d'organisation (rôle relationnel avec l'entreprise) ─────
    public const TYPES = [
        'client'         => 'Client',
        'fournisseur'    => 'Fournisseur',
        'investisseur'   => 'Investisseur',
        'administration' => 'Administration',
        'partenaire'     => 'Partenaire',
        'autre'          => 'Autre',
    ];

    public const TYPE_COULEURS = [
        'client'         => 'success',
        'fournisseur'    => 'primary',
        'investisseur'   => 'warning',
        'administration' => 'info',
        'partenaire'     => 'secondary',
        'autre'          => 'dark',
    ];

    public const TYPE_ICONES = [
        'client'         => 'fa-handshake',
        'fournisseur'    => 'fa-truck',
        'investisseur'   => 'fa-chart-line',
        'administration' => 'fa-building-columns',
        'partenaire'     => 'fa-people-arrows',
        'autre'          => 'fa-building',
    ];

    /**
     * Statuts de relation contextualisés par type d'organisation.
     * Chaque type propose son propre cycle de vie relationnel.
     */
    public const STATUTS_RELATION = [
        'client' => [
            'prospect'       => 'Prospect',
            'en_negociation' => 'En négociation',
            'actif'          => 'Client actif',
            'inactif'        => 'Inactif',
            'perdu'          => 'Perdu',
        ],
        'fournisseur' => [
            'evaluation' => "En cours d'évaluation",
            'reference'  => 'Référencé',
            'actif'      => 'Actif',
            'suspendu'   => 'Suspendu',
            'radie'      => 'Radié',
        ],
        'investisseur' => [
            'prospect'      => 'Prospect',
            'en_discussion' => 'En discussion',
            'actif'         => 'Investisseur actif',
            'sortie'        => 'Sortie',
        ],
        'administration' => [
            'actif'   => 'Actif',
            'inactif' => 'Inactif',
        ],
        'partenaire' => [
            'prospect' => 'Prospect',
            'actif'    => 'Partenaire actif',
            'ancien'   => 'Ancien partenaire',
        ],
        'autre' => [
            'actif'   => 'Actif',
            'inactif' => 'Inactif',
        ],
    ];

    public const STATUT_RELATION_COULEURS = [
        'prospect'       => 'info',
        'en_negociation' => 'warning',
        'en_discussion'  => 'warning',
        'actif'          => 'success',
        'reference'      => 'primary',
        'evaluation'     => 'info',
        'inactif'        => 'secondary',
        'suspendu'       => 'warning',
        'perdu'          => 'danger',
        'radie'          => 'danger',
        'sortie'         => 'dark',
        'ancien'         => 'secondary',
    ];

    protected $appends = ['logo_url', 'media_url'];

    protected static function booted(): void
    {
        static::creating(function (ContactOrganisation $o) {
            if (empty($o->slug)) {
                $o->slug = static::genererSlugUnique($o->nom);
            }
        });
    }

    protected static function genererSlugUnique(string $base): string
    {
        $slug = Str::slug($base) ?: 'organisation';
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    // ── Relations ──────────────────────────────────────────
    public function contacts()
    {
        return $this->hasMany(Contact::class, 'organisation_id');
    }

    public function opportunites()
    {
        return $this->hasMany(Opportunite::class, 'organisation_id');
    }

    public function secteur()
    {
        return $this->belongsTo(SecteurActivite::class, 'secteur_id');
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interactions(): MorphMany
    {
        return $this->morphMany(CrmInteraction::class, 'interactable')->orderByDesc('date_interaction');
    }

    public function documents()
    {
        return $this->hasMany(OrganisationDocument::class, 'organisation_id');
    }

    /**
     * Liste des types de documents attendus pour ce type d'organisation,
     * avec l'état de complétude (fourni ou non, expiré, etc.).
     *
     * Retourne une collection : [
     *   ['type_document' => TypeDocument, 'obligatoire' => bool, 'documents' => Collection<OrganisationDocument>],
     *   …
     * ]
     */
    public function documentsAttendus()
    {
        if (!$this->type) return collect();

        $types = TypeDocument::actif()
            ->with(['exigences' => fn($q) => $q->where('type_organisation', $this->type)])
            ->whereHas('exigences', fn($q) => $q->where('type_organisation', $this->type))
            ->orderBy('ordre')->orderBy('libelle')
            ->get();

        $docsFournis = $this->documents()->with('typeDocument')->get()->groupBy('type_document_id');

        return $types->map(fn($t) => [
            'type_document' => $t,
            'obligatoire'   => $t->estObligatoirePour($this->type),
            'documents'     => $docsFournis->get($t->id, collect()),
            'est_fourni'    => $docsFournis->has($t->id),
        ]);
    }

    public function getCompletudeDocumentsAttribute(): array
    {
        $attendus = $this->documentsAttendus();
        $obligatoires   = $attendus->where('obligatoire', true);
        $obligFournis   = $obligatoires->where('est_fourni', true)->count();
        $obligTotal     = $obligatoires->count();
        return [
            'obligatoires_fournis' => $obligFournis,
            'obligatoires_total'   => $obligTotal,
            'pct'                  => $obligTotal ? round($obligFournis / $obligTotal * 100) : 100,
            'complet'              => $obligTotal === $obligFournis,
        ];
    }

    // ── Accessors ──────────────────────────────────────────
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) return null;
        return asset('storage/' . ltrim($this->logo, '/'));
    }

    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getInitialesAttribute(): string
    {
        $words = preg_split('/\s+/', trim($this->nom));
        return strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
    }

    public function getTypeLibelleAttribute(): string { return self::TYPES[$this->type] ?? 'Autre'; }
    public function getTypeCouleurAttribute(): string { return self::TYPE_COULEURS[$this->type] ?? 'secondary'; }
    public function getTypeIconeAttribute(): string   { return self::TYPE_ICONES[$this->type] ?? 'fa-building'; }
    public function getNomAffichageAttribute(): string { return $this->raison_sociale ?: $this->nom; }

    public function getStatutRelationLibelleAttribute(): ?string
    {
        if (!$this->statut_relation) return null;
        return self::STATUTS_RELATION[$this->type][$this->statut_relation] ?? $this->statut_relation;
    }

    public function getStatutRelationCouleurAttribute(): string
    {
        return self::STATUT_RELATION_COULEURS[$this->statut_relation] ?? 'secondary';
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeType(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('type', $type) : $q;
    }
    public function scopeClient(Builder $q): Builder         { return $q->where('type', 'client'); }
    public function scopeFournisseur(Builder $q): Builder    { return $q->where('type', 'fournisseur'); }
    public function scopeInvestisseur(Builder $q): Builder   { return $q->where('type', 'investisseur'); }
    public function scopeAdministration(Builder $q): Builder { return $q->where('type', 'administration'); }
    public function scopeActif(Builder $q): Builder          { return $q->where('statut', 1); }

    /** Legacy — conservé pour compatibilité vues CRM historiques. */
    public function scopeClients(Builder $q): Builder
    {
        return $q->where(function ($w) {
            $w->where('type', 'client')->orWhere('est_client', true);
        });
    }

    public function scopeProspects(Builder $q): Builder
    {
        return $q->where('est_prospect', true);
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('nom',            'like', "%{$terme}%")
              ->orWhere('raison_sociale','like', "%{$terme}%")
              ->orWhere('email',        'like', "%{$terme}%")
              ->orWhere('ville',        'like', "%{$terme}%")
              ->orWhere('siret',        'like', "%{$terme}%")
              ->orWhere('code',         'like', "%{$terme}%")
              ->orWhere('nif',          'like', "%{$terme}%");
        });
    }
}
