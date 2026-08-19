<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    protected $fillable = [
        'code', 'nom', 'filename', 'type', 'entite_id',
        'solde', 'solde_initial', 'devise', 'actif',
        'rib', 'responsable', 'gestionnaire',
        'contact_gestionnaire', 'domiciliation',
        'attribut1', 'attribut2', 'attribut3', 'effacer',
    ];

    protected $casts = [
        'actif'          => 'boolean',
        'solde'          => 'float',
        'solde_initial'  => 'float',
    ];

    // ─── Types de compte ───────────────────────
    public const TYPE_BANQUE       = 1;
    public const TYPE_CAISSE       = 2;
    public const TYPE_TIERS        = 3;
    public const TYPE_ELECTRONIQUE = 4;

    public const TYPES = [
        self::TYPE_BANQUE       => 'Bancaire',
        self::TYPE_CAISSE       => 'Caisse (numéraire)',
        self::TYPE_ELECTRONIQUE => 'Électronique (Mobile Money, e-wallet)',
        self::TYPE_TIERS        => 'Tiers',
    ];

    public const TYPE_COULEURS = [
        self::TYPE_BANQUE       => 'primary',
        self::TYPE_CAISSE       => 'warning',
        self::TYPE_ELECTRONIQUE => 'info',
        self::TYPE_TIERS        => 'secondary',
    ];

    public const TYPE_ICONES = [
        self::TYPE_BANQUE       => 'fa-building-columns',
        self::TYPE_CAISSE       => 'fa-cash-register',
        self::TYPE_ELECTRONIQUE => 'fa-mobile-screen-button',
        self::TYPE_TIERS        => 'fa-handshake',
    ];

    /**
     * Types de comptes de trésorerie (excluant les comptes tiers).
     * Utilisé pour lister les comptes qu'on peut débiter/créditer via un ordre.
     */
    public const TYPES_TRESORERIE = [
        self::TYPE_BANQUE, self::TYPE_CAISSE, self::TYPE_ELECTRONIQUE,
    ];

    /**
     * Modes de règlement acceptés selon le type de compte.
     * Sert à filtrer les comptes proposés dans le formulaire d'ordre
     * une fois qu'un mode de règlement a été choisi.
     */
    public const MODES_PAR_TYPE = [
        self::TYPE_BANQUE       => ['cheque', 'virement'],
        self::TYPE_CAISSE       => ['numeraire'],
        self::TYPE_ELECTRONIQUE => ['virement'], // Mobile Money = virement électronique
    ];

    public function transactionComptes()
    {
        return $this->hasMany(TransactionCompte::class, 'compte_id');
    }

    public function grandLivres()
    {
        return $this->hasMany(GrandLivre::class, 'compte_id');
    }

    public function scopeBanque($query)       { return $query->where('type', self::TYPE_BANQUE); }
    public function scopeCaisse($query)       { return $query->where('type', self::TYPE_CAISSE); }
    public function scopeElectronique($query) { return $query->where('type', self::TYPE_ELECTRONIQUE); }
    public function scopeTiers($query)        { return $query->where('type', self::TYPE_TIERS); }
    public function scopeActif($query)        { return $query->where('actif', true)->where('effacer', 0); }

    /**
     * Comptes de trésorerie (banque + caisse + électronique), utilisables dans un ordre.
     */
    public function scopeTresorerie($query)
    {
        return $query->whereIn('type', self::TYPES_TRESORERIE);
    }

    /**
     * Comptes compatibles avec un mode de règlement donné.
     * numeraire → caisse ; cheque → banque ; virement → banque + électronique
     */
    public function scopeModeReglement($query, ?string $mode)
    {
        if (!$mode) return $query;
        $types = collect(self::MODES_PAR_TYPE)
            ->filter(fn($modes) => in_array($mode, $modes, true))
            ->keys();
        return $types->isNotEmpty() ? $query->whereIn('type', $types) : $query->whereRaw('1=0');
    }

    // ─── Accessors ──────────────────────────────
    public function getTypeLibelleAttribute(): string { return self::TYPES[$this->type] ?? '—'; }
    public function getTypeCouleurAttribute(): string { return self::TYPE_COULEURS[$this->type] ?? 'secondary'; }
    public function getTypeIconeAttribute(): string   { return self::TYPE_ICONES[$this->type] ?? 'fa-wallet'; }

    public function getEstTresorerieAttribute(): bool
    {
        return in_array($this->type, self::TYPES_TRESORERIE, true);
    }

    /**
     * Ordres exécutés impactant ce compte.
     */
    public function ordres()
    {
        return $this->hasMany(\App\Models\Finance\Ordre::class, 'compte_id');
    }

    /**
     * Ajuste le solde du compte suite à une opération (positif = crédit, négatif = débit).
     * Retourne le nouveau solde.
     */
    public function ajusterSolde(float $delta): float
    {
        $this->increment('solde', $delta);
        return (float) $this->fresh()->solde;
    }
}
