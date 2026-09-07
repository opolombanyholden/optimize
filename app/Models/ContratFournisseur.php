<?php

namespace App\Models;

use App\Models\FrequencePaiement;
use App\Models\Intranet\ContactOrganisation;
use App\Models\TypeEngagement;
use App\Traits\Intranet\HasPiecesJointes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContratFournisseur extends Model
{
    use SoftDeletes, HasPiecesJointes;

    protected $table = 'contrats_fournisseur';

    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_ACTIF     = 'actif';
    public const STATUT_EXPIRE    = 'expire';
    public const STATUT_RESILIE   = 'resilie';
    public const STATUT_RENOUVELE = 'renouvele';

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_ACTIF     => 'Actif',
        self::STATUT_EXPIRE    => 'Expiré',
        self::STATUT_RESILIE   => 'Résilié',
        self::STATUT_RENOUVELE => 'Renouvelé',
    ];

    public const TYPES = [
        'achat'       => 'Achat',
        'prestation'  => 'Prestation',
        'cadre'       => 'Accord-cadre',
        'maintenance' => 'Maintenance',
        'licence'     => 'Licence / abonnement',
        'autre'       => 'Autre',
    ];

    public const FREQUENCES_PAIEMENT = [
        'ponctuel'    => 'Ponctuel (paiement unique)',
        'mensuel'     => 'Mensuel',
        'bimestriel'  => 'Bimestriel (tous les 2 mois)',
        'trimestriel' => 'Trimestriel',
        'semestriel'  => 'Semestriel',
        'annuel'      => 'Annuel',
    ];

    protected $fillable = [
        'reference', 'fournisseur_id', 'objet', 'description', 'type',
        'date_signature', 'date_debut', 'date_fin',
        'montant_ht', 'montant_ttc', 'devise',
        'frequence_paiement', 'montant_par_paiement', 'jour_paiement', 'delai_paiement_jours',
        'statut', 'renouvellement_auto', 'preavis_resiliation_jours',
        'conditions', 'parent_contrat_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_signature' => 'date',
            'date_debut'     => 'date',
            'date_fin'       => 'date',
            'montant_ht'     => 'decimal:2',
            'montant_ttc'    => 'decimal:2',
            'montant_par_paiement' => 'decimal:2',
            'jour_paiement'  => 'integer',
            'delai_paiement_jours' => 'integer',
            'renouvellement_auto'      => 'boolean',
            'preavis_resiliation_jours'=> 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $c) {
            if (empty($c->reference)) $c->reference = self::genererReference();
        });
    }

    public static function genererReference(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('CTR-%s-%04d', $year, $count);
    }

    public function fournisseur() { return $this->belongsTo(ContactOrganisation::class, 'fournisseur_id'); }
    public function auteur()      { return $this->belongsTo(User::class, 'created_by'); }
    public function parent()      { return $this->belongsTo(self::class, 'parent_contrat_id'); }
    public function enfants()     { return $this->hasMany(self::class, 'parent_contrat_id'); }
    public function avenants()    { return $this->hasMany(ContratFournisseurAvenant::class, 'contrat_id')->orderBy('numero'); }

    // ── Accessors ──────────────────────────────────────────
    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }

    public function getTypeLibelleAttribute(): string
    {
        // Priorité : référentiel dynamique ; fallback = constante historique
        $t = TypeEngagement::where('code', $this->type)->first();
        return $t?->libelle ?? (self::TYPES[$this->type] ?? '—');
    }

    public function getJoursAvantExpirationAttribute(): ?int
    {
        if (!$this->date_fin) return null;
        return (int) now()->startOfDay()->diffInDays($this->date_fin->startOfDay(), false);
    }

    public function getEstExpireAttribute(): bool
    {
        return $this->date_fin && $this->date_fin->lt(now()->startOfDay());
    }

    public function getEstProchainExpirationAttribute(): bool
    {
        if (!$this->date_fin || $this->statut !== self::STATUT_ACTIF) return false;
        $jours = $this->jours_avant_expiration;
        return $jours !== null && $jours >= 0 && $jours <= 60;
    }

    public function getFrequencePaiementLibelleAttribute(): string
    {
        $f = FrequencePaiement::where('code', $this->frequence_paiement)->first();
        return $f?->libelle ?? (self::FREQUENCES_PAIEMENT[$this->frequence_paiement] ?? '—');
    }

    /**
     * Calcule la prochaine date d'échéance de paiement, selon la fréquence + jour paiement.
     * Ne calcule que pour les contrats actifs récurrents.
     */
    public function getProchaineEcheanceAttribute(): ?Carbon
    {
        if (!in_array($this->statut, [self::STATUT_ACTIF], true)) return null;
        $freq = $this->frequence_paiement;
        if (!$freq || $freq === 'ponctuel') return null;
        $moisIncr = [
            'mensuel'     => 1,
            'bimestriel'  => 2,
            'trimestriel' => 3,
            'semestriel'  => 6,
            'annuel'      => 12,
        ][$freq] ?? null;
        if (!$moisIncr) return null;

        $today = now()->startOfDay();
        $jour = max(1, min(28, (int) ($this->jour_paiement ?: ($this->date_debut?->day ?? 1)))); // clamp 1-28 pour éviter les mois courts

        // On part de la date de début, on avance de $moisIncr jusqu'à dépasser aujourd'hui
        $candidate = ($this->date_debut ?: $today)->copy()->day($jour);
        while ($candidate->lt($today)) {
            $candidate->addMonths($moisIncr);
        }
        return $candidate;
    }

    // ── Actions workflow ───────────────────────────────────
    public function activer(): void
    {
        $this->update(['statut' => self::STATUT_ACTIF]);
    }
    public function resilier(?string $motif = null): void
    {
        $this->update(['statut' => self::STATUT_RESILIE, 'conditions' => $motif ? ($this->conditions."\n\n[Résiliation] ".$motif) : $this->conditions]);
    }
    public function marquerExpire(): void
    {
        $this->update(['statut' => self::STATUT_EXPIRE]);
    }
    /**
     * Crée un nouveau contrat "enfant" pour le renouvellement, marque l'ancien renouvelé.
     */
    public function renouveler(array $data): self
    {
        $nouveau = self::create(array_merge([
            'fournisseur_id' => $this->fournisseur_id,
            'objet'          => $this->objet,
            'description'    => $this->description,
            'type'           => $this->type,
            'montant_ht'     => $this->montant_ht,
            'montant_ttc'    => $this->montant_ttc,
            'devise'         => $this->devise,
            'renouvellement_auto'      => $this->renouvellement_auto,
            'preavis_resiliation_jours'=> $this->preavis_resiliation_jours,
            'statut'         => self::STATUT_ACTIF,
            'parent_contrat_id' => $this->id,
            'created_by'     => auth()->id() ?? $this->created_by,
        ], $data));
        $this->update(['statut' => self::STATUT_RENOUVELE]);
        return $nouveau;
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeActifs($q) { return $q->where('statut', self::STATUT_ACTIF); }
    public function scopeProcheExpiration($q, int $jours = 60)
    {
        return $q->where('statut', self::STATUT_ACTIF)
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now()->startOfDay(), now()->addDays($jours)->endOfDay()]);
    }
}
