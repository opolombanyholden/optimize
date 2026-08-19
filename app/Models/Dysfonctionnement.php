<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dysfonctionnement extends Model
{
    use SoftDeletes, HasPiecesJointes;

    public const STATUT_SIGNALE = 0;
    public const STATUT_PRIS_EN_CHARGE = 1;
    public const STATUT_RESOLU = 2;
    public const STATUT_FERME = 3;
    public const STATUT_ANNULE = 4;

    public const STATUTS = [
        self::STATUT_SIGNALE => 'Signalé',
        self::STATUT_PRIS_EN_CHARGE => 'Pris en charge',
        self::STATUT_RESOLU => 'Résolu',
        self::STATUT_FERME => 'Fermé',
        self::STATUT_ANNULE => 'Annulé',
    ];

    public const PRIORITES = [
        'basse' => 'Basse',
        'normale' => 'Normale',
        'haute' => 'Haute',
        'critique' => 'Critique',
    ];

    protected $fillable = [
        'ticket_ref',
        'label', 'description', 'type_id', 'thematique_id', 'declarant_id',
        'immobilisation_id', 'localisation', 'priorite',
        'priorite_admin', 'moment_intervention', 'priorise_par', 'priorise_at',
        'date_signalement', 'date_resolution',
        'pris_en_charge_par', 'pris_en_charge_at',
        'resolu_par', 'ferme_par', 'date_fermeture',
        'commentaire_resolution',
        'fichiersjoin', 'statut', 'extra_attributes',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $d) {
            if (empty($d->ticket_ref)) {
                $count = static::whereYear('created_at', date('Y'))->count() + 1;
                $d->ticket_ref = sprintf('TCK-%s-%04d', date('Y'), $count);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date_signalement' => 'datetime',
            'date_resolution' => 'datetime',
            'pris_en_charge_at' => 'datetime',
            'date_fermeture' => 'datetime',
            'moment_intervention' => 'datetime',
            'priorise_at' => 'datetime',
            'statut' => 'integer',
            'extra_attributes' => 'array',
        ];
    }

    public function thematique() { return $this->belongsTo(MgThematique::class, 'thematique_id'); }
    public function priseur() { return $this->belongsTo(User::class, 'priorise_par'); }

    /**
     * Priorise le ticket (par un admin habilité).
     */
    public function prioriser(string $prioriteAdmin, ?\DateTimeInterface $moment = null, ?int $userId = null): void
    {
        $this->update([
            'priorite_admin' => $prioriteAdmin,
            'moment_intervention' => $moment,
            'priorise_par' => $userId ?? auth()->id(),
            'priorise_at' => now(),
        ]);
    }

    public function type() { return $this->belongsTo(TypeDysfonctionnement::class, 'type_id'); }
    public function declarant() { return $this->belongsTo(User::class, 'declarant_id'); }
    public function immobilisation() { return $this->belongsTo(Immobilisation::class, 'immobilisation_id'); }
    public function interventions() { return $this->hasMany(Intervention::class, 'dysfonctionnement_id'); }
    public function priseurEnCharge() { return $this->belongsTo(User::class, 'pris_en_charge_par'); }
    public function resoluteur() { return $this->belongsTo(User::class, 'resolu_par'); }

    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? 'Inconnu';
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_SIGNALE => 'secondary',
            self::STATUT_PRIS_EN_CHARGE => 'info',
            self::STATUT_RESOLU => 'success',
            self::STATUT_FERME => 'dark',
            self::STATUT_ANNULE => 'warning',
            default => 'light',
        };
    }

    public function peutEtrePrisEnCharge(): bool { return $this->statut === self::STATUT_SIGNALE; }
    public function peutEtreResolu(): bool { return in_array($this->statut, [self::STATUT_SIGNALE, self::STATUT_PRIS_EN_CHARGE], true); }
    public function peutEtreFerme(): bool { return $this->statut === self::STATUT_RESOLU; }
    public function peutEtreAnnule(): bool { return !in_array($this->statut, [self::STATUT_FERME, self::STATUT_ANNULE], true); }

    public function prendreEnCharge(?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_PRIS_EN_CHARGE,
            'pris_en_charge_par' => $userId ?? auth()->id(),
            'pris_en_charge_at' => now(),
        ]);
    }

    public function resoudre(?string $commentaire = null, ?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_RESOLU,
            'date_resolution' => now(),
            'resolu_par' => $userId ?? auth()->id(),
            'commentaire_resolution' => $commentaire ?: $this->commentaire_resolution,
        ]);
    }

    public function fermer(?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_FERME,
            'date_fermeture' => now(),
            'ferme_par' => $userId ?? auth()->id(),
        ]);
    }
}
