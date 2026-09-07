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
        'resolution_proposee_at', 'resolution_proposee_par', 'commentaire_resolution_proposee',
        'validation_resolution_at', 'validation_resolution_par', 'commentaire_validation_resolution',
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
            'resolution_proposee_at' => 'datetime',
            'validation_resolution_at' => 'datetime',
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
    public function proposeurResolution() { return $this->belongsTo(User::class, 'resolution_proposee_par'); }
    public function validateurResolution() { return $this->belongsTo(User::class, 'validation_resolution_par'); }

    /**
     * Une résolution peut être proposée si le ticket est ouvert (signalé ou en cours de traitement)
     * et qu'aucune proposition en attente n'existe déjà.
     */
    public function peutRecevoirPropositionResolution(): bool
    {
        return in_array($this->statut, [self::STATUT_SIGNALE, self::STATUT_PRIS_EN_CHARGE], true)
            && $this->resolution_proposee_at === null;
    }

    /**
     * Vrai si une résolution est proposée mais pas encore validée par l'émetteur.
     */
    public function attendValidationResolution(): bool
    {
        return $this->resolution_proposee_at !== null
            && $this->validation_resolution_at === null
            && $this->statut !== self::STATUT_RESOLU;
    }

    /**
     * Le déclarant (émetteur) est-il l'utilisateur donné ?
     */
    public function emetteurEst(?int $userId): bool
    {
        return $userId !== null && (int) $this->declarant_id === (int) $userId;
    }

    /**
     * Étape 1 : le technicien propose la résolution après avoir terminé son intervention.
     * Ne change pas encore le statut à RESOLU — attend la validation de l'émetteur.
     */
    public function proposerResolution(?string $commentaire = null, ?int $userId = null): void
    {
        $this->update([
            'resolution_proposee_at' => now(),
            'resolution_proposee_par' => $userId ?? auth()->id(),
            'commentaire_resolution_proposee' => $commentaire,
        ]);
    }

    /**
     * Étape 2 : l'émetteur valide la résolution proposée → passage à RESOLU.
     */
    public function validerResolutionParEmetteur(?string $commentaire = null, ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();
        $this->update([
            'validation_resolution_at' => now(),
            'validation_resolution_par' => $userId,
            'commentaire_validation_resolution' => $commentaire,
            'statut' => self::STATUT_RESOLU,
            'date_resolution' => now(),
            'resolu_par' => $userId,
            'commentaire_resolution' => $commentaire ?? $this->commentaire_resolution_proposee,
        ]);
    }

    /**
     * Rejet par l'émetteur : la proposition est annulée, le ticket reste en cours.
     */
    public function rejeterResolutionParEmetteur(?string $motif = null, ?int $userId = null): void
    {
        $this->update([
            'resolution_proposee_at' => null,
            'resolution_proposee_par' => null,
            'commentaire_resolution_proposee' => null,
            'commentaire_validation_resolution' => $motif,
        ]);
    }

    /**
     * Toutes les assignations (users, services, groupes) — actives + retirées.
     */
    public function assignations()
    {
        return $this->hasMany(DysfonctionnementAssignation::class, 'dysfonctionnement_id');
    }

    /**
     * Assignations actives (non retirées).
     */
    public function assignationsActives()
    {
        return $this->assignations()->whereNull('retire_at')->with('assignable', 'assigneur');
    }

    /**
     * Assigne le ticket à un ou plusieurs destinataires (User, Service ou Groupe).
     * $cibles = [['type' => 'user'|'service'|'groupe', 'id' => 42], ...]
     * Idempotent : une même paire (type,id) déjà présente n'est pas dupliquée.
     */
    public function assignerA(array $cibles, ?string $commentaire = null, ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();
        $map = [
            'user'    => User::class,
            'service' => \App\Models\Intranet\Service::class,
            'groupe'  => \App\Models\Intranet\Groupe::class,
            'entite'  => \App\Models\Organisation::class,
        ];
        foreach ($cibles as $c) {
            $type = $c['type'] ?? null;
            $id   = (int) ($c['id'] ?? 0);
            if (!isset($map[$type]) || $id <= 0) continue;
            DysfonctionnementAssignation::firstOrCreate(
                [
                    'dysfonctionnement_id' => $this->id,
                    'assignable_type'      => $map[$type],
                    'assignable_id'        => $id,
                ],
                [
                    'assigne_par'  => $userId,
                    'commentaire'  => $commentaire,
                    'assigne_at'   => now(),
                ],
            );
        }
    }

    /**
     * Retire toutes les assignations actives puis rejoue $cibles (sync).
     */
    public function synchroniserAssignations(array $cibles, ?int $userId = null): void
    {
        $this->assignations()->whereNull('retire_at')->update(['retire_at' => now()]);
        $this->assignerA($cibles, null, $userId);
    }

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
