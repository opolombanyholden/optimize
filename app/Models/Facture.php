<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Facture extends Model
{
    use SoftDeletes;
    use LogsActivity;
    use HasPiecesJointes;

    protected $fillable = [
        'numero', 'sens',
        'tiers_type', 'tiers_id', 'tiers_source', 'tiers_infos_json',
        'exercice_id',
        'date_emission', 'date_echeance', 'reference_externe', 'objet',
        'montant_ht', 'taux_tva', 'montant_tva', 'montant_ttc', 'montant_regle',
        'statut', 'valide_par', 'valide_at', 'annule_par', 'annule_at', 'motif_annulation',
        'ordonnancee_at', 'ordonnee_par', 'motivation_ordonnancement',
        'commentaire', 'created_by', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_emission' => 'date',
            'date_echeance' => 'date',
            'valide_at'     => 'datetime',
            'annule_at'     => 'datetime',
            'ordonnancee_at' => 'datetime',
            'montant_ht'    => 'decimal:2',
            'taux_tva'      => 'decimal:2',
            'montant_tva'   => 'decimal:2',
            'montant_ttc'   => 'decimal:2',
            'montant_regle' => 'decimal:2',
            'extra_attributes'  => 'array',
            'tiers_infos_json'  => 'array',
        ];
    }

    public const TIERS_SOURCE_ORGANISATION = 'organisation';
    public const TIERS_SOURCE_EXTERNE      = 'externe';

    public const STATUTS = [
        0 => 'Brouillon',
        1 => 'Validée',
        2 => 'Partiellement réglée',
        3 => 'Réglée',
        4 => 'Annulée',
    ];

    public const STATUT_COULEURS = [
        0 => 'secondary',
        1 => 'info',
        2 => 'warning',
        3 => 'success',
        4 => 'danger',
    ];

    public const SENS = [
        'depense' => 'Dépense (fournisseur)',
        'recette' => 'Recette (client)',
    ];

    /**
     * Types de tiers autorisés pour une Facture.
     * Depuis la fusion Client/Fournisseur → Organisation typée (2026-07-09),
     * tous les tiers sont des \App\Models\Intranet\ContactOrganisation filtrés sur `type`.
     */
    public const TIERS_TYPES = [
        'fournisseur' => \App\Models\Intranet\ContactOrganisation::class,
        'client'      => \App\Models\Intranet\ContactOrganisation::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'sens', 'montant_ttc', 'statut'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('facture')
            ->setDescriptionForEvent(fn(string $event) => "Facture {$event}");
    }

    public function tiers()
    {
        // Polymorphique manuel : tiers_type stocke 'fournisseur' ou 'client', pas la classe
        return $this->morphTo(null, 'tiers_type', 'tiers_id');
    }

    public function exercice()  { return $this->belongsTo(Exercice::class); }
    public function validateur(){ return $this->belongsTo(User::class, 'valide_par'); }
    public function annuleur()  { return $this->belongsTo(User::class, 'annule_par'); }
    public function createur()  { return $this->belongsTo(User::class, 'created_by'); }
    public function operations() { return $this->hasMany(OperationFinanciere::class); }
    public function ordres()     { return $this->hasMany(\App\Models\Finance\Ordre::class, 'facture_id'); }
    public function ordonnateur(){ return $this->belongsTo(\App\Models\User::class, 'ordonnee_par'); }
    public function devisSource(){ return $this->hasOne(\App\Models\DevisFournisseur::class, 'facture_id'); }

    public function estOrdonnancee(): bool { return $this->ordonnancee_at !== null; }
    public function peutEtreOrdonnancee(): bool { return !$this->estOrdonnancee() && $this->sens === 'depense'; }

    /**
     * Donne le OK pour paiement (rôle ordonnateur). Nécessaire avant tout ordre de paiement.
     */
    public function ordonnancer(?string $motivation = null, ?int $userId = null): void
    {
        $this->update([
            'ordonnancee_at' => now(),
            'ordonnee_par'   => $userId ?? auth()->id(),
            'motivation_ordonnancement' => $motivation,
        ]);
    }

    /**
     * Retourne le tiers résolu, avec fallback sur les infos externes si le tiers
     * n'est pas enregistré. Uniformise l'accès pour les vues.
     */
    public function getTiersLibelleAttribute(): ?string
    {
        if ($this->tiers_source === self::TIERS_SOURCE_EXTERNE) {
            return $this->tiers_infos_json['nom']
                ?? $this->tiers_infos_json['raison_sociale']
                ?? '— Tiers externe —';
        }
        return $this->tiersResolu()?->nom_affichage
            ?? $this->tiersResolu()?->raison_sociale
            ?? $this->tiersResolu()?->nom;
    }

    /**
     * Somme des ordres EXÉCUTÉS rattachés à cette facture (ce qui a effectivement
     * été payé/encaissé au Grand Livre).
     */
    public function getMontantOrdresExecutesAttribute(): float
    {
        return (float) $this->ordres()
            ->where('statut', \App\Models\Finance\Ordre::STATUT_EXECUTE)
            ->sum('montant');
    }

    /**
     * Solde restant à payer/encaisser sur cette facture.
     */
    public function getSoldeRestantAttribute(): float
    {
        return round((float) $this->montant_ttc - $this->montant_ordres_executes, 2);
    }

    /**
     * Résout le tiers en Organisation filtrée sur le type approprié.
     * Depuis la fusion, tiers_type = 'client'|'fournisseur' → ContactOrganisation avec type correspondant.
     */
    public function tiersResolu()
    {
        if (!$this->tiers_id) return null;
        $class = self::TIERS_TYPES[$this->tiers_type] ?? null;
        if (!$class) return null;
        if ($class === \App\Models\Intranet\ContactOrganisation::class) {
            return \App\Models\Intranet\ContactOrganisation::where('id', $this->tiers_id)
                ->where('type', $this->tiers_type)
                ->first();
        }
        return $class::find($this->tiers_id);
    }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? '—'; }
    public function getStatutCouleurAttribute(): string { return self::STATUT_COULEURS[$this->statut] ?? 'secondary'; }
    public function getSensLibelleAttribute(): string   { return self::SENS[$this->sens] ?? $this->sens; }

    public function getMontantResteAAttribute(): float
    {
        return max(0, (float) $this->montant_ttc - (float) $this->montant_regle);
    }

    public function getEstModifiableAttribute(): bool  { return $this->statut === 0; }
    public function getEstValidableAttribute(): bool   { return $this->statut === 0; }
    public function getEstAnnulableAttribute(): bool   { return in_array($this->statut, [0, 1, 2]); }

    public function scopeDepense($q) { return $q->where('sens', 'depense'); }
    public function scopeRecette($q) { return $q->where('sens', 'recette'); }
    public function scopeBrouillon($q) { return $q->where('statut', 0); }
    public function scopeValidee($q) { return $q->where('statut', 1); }
    public function scopeReglee($q)  { return $q->where('statut', 3); }
}
