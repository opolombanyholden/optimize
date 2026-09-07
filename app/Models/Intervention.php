<?php

namespace App\Models;

use App\Traits\Intranet\HasPiecesJointes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intervention extends Model
{
    use SoftDeletes, HasPiecesJointes;

    public const STATUT_PLANIFIEE = 0;
    public const STATUT_EN_COURS = 1;
    public const STATUT_TERMINEE = 2;
    public const STATUT_ANNULEE = 3;

    public const STATUTS = [
        self::STATUT_PLANIFIEE => 'Planifiée',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_TERMINEE => 'Terminée',
        self::STATUT_ANNULEE => 'Annulée',
    ];

    public const TYPES = [
        'preventive' => 'Préventive',
        'corrective' => 'Corrective',
        'curative' => 'Curative',
        'ameliorative' => 'Améliorative',
    ];

    public const RESOLUTION_RESOLU = 'resolu';
    public const RESOLUTION_PARTIEL = 'partiel';
    public const RESOLUTION_NON_RESOLU = 'non_resolu';

    public const RESOLUTIONS = [
        self::RESOLUTION_RESOLU => 'Résolu',
        self::RESOLUTION_PARTIEL => 'Partiellement résolu',
        self::RESOLUTION_NON_RESOLU => 'Non résolu',
    ];

    protected $fillable = [
        'label', 'description', 'dysfonctionnement_id', 'thematique_id',
        'immobilisation_id', 'type_id', 'technicien_id', 'type_intervention', 'nature_id',
        'date_planifiee', 'date_debut', 'date_fin',
        'cout', 'rapport',
        'nature_probleme', 'pistes_solution', 'solution_appliquee', 'resultat', 'preuve',
        'statut_resolution',
        'demarree_par', 'terminee_par', 'annulee_par', 'annulee_at', 'motif_annulation',
        'fichiersjoin', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_planifiee' => 'datetime',
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'annulee_at' => 'datetime',
            'cout' => 'decimal:2',
            'statut' => 'integer',
            'extra_attributes' => 'array',
        ];
    }

    public function dysfonctionnement() { return $this->belongsTo(Dysfonctionnement::class, 'dysfonctionnement_id'); }
    public function immobilisation() { return $this->belongsTo(Immobilisation::class, 'immobilisation_id'); }
    public function type() { return $this->belongsTo(TypeDysfonctionnement::class, 'type_id'); }
    public function nature() { return $this->belongsTo(NatureIntervention::class, 'nature_id'); }
    public function technicien() { return $this->belongsTo(User::class, 'technicien_id'); }
    public function thematique() { return $this->belongsTo(MgThematique::class, 'thematique_id'); }

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut] ?? 'Inconnu'; }
    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            self::STATUT_PLANIFIEE => 'secondary',
            self::STATUT_EN_COURS => 'info',
            self::STATUT_TERMINEE => 'success',
            self::STATUT_ANNULEE => 'warning',
            default => 'light',
        };
    }

    public function peutEtreDemarree(): bool { return $this->statut === self::STATUT_PLANIFIEE; }
    public function peutEtreTerminee(): bool { return $this->statut === self::STATUT_EN_COURS; }
    public function peutEtreAnnulee(): bool { return in_array($this->statut, [self::STATUT_PLANIFIEE, self::STATUT_EN_COURS], true); }

    public function demarrer(?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_EN_COURS,
            'date_debut' => now(),
            'demarree_par' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Termine l'intervention. Le dysfonctionnement associé n'est marqué résolu QUE si
     * l'agent indique statut_resolution = 'resolu'. Sinon (partiel/non résolu), le ticket
     * reste ouvert et pourra recevoir de nouvelles interventions.
     */
    public function terminer(array $data = [], ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();

        $this->update(array_merge([
            'statut' => self::STATUT_TERMINEE,
            'date_fin' => now(),
            'terminee_par' => $userId,
        ], array_filter([
            'rapport' => $data['rapport'] ?? null,
            'cout' => $data['cout'] ?? null,
            'nature_probleme' => $data['nature_probleme'] ?? null,
            'pistes_solution' => $data['pistes_solution'] ?? null,
            'solution_appliquee' => $data['solution_appliquee'] ?? null,
            'resultat' => $data['resultat'] ?? null,
            'preuve' => $data['preuve'] ?? null,
            'statut_resolution' => $data['statut_resolution'] ?? null,
        ], fn ($v) => $v !== null && $v !== '')));

        // Si résolution proposée : le ticket bascule en "résolution proposée"
        // et attend la validation de l'émetteur (déclarant) — il ne passe PAS
        // à RESOLU directement.
        if ($this->dysfonctionnement
            && $this->statut_resolution === self::RESOLUTION_RESOLU
            && $this->dysfonctionnement->peutRecevoirPropositionResolution()) {
            $this->dysfonctionnement->proposerResolution(
                "Intervention #{$this->id} terminée avec proposition de résolution.",
                $userId
            );
        }
    }

    public function annuler(string $motif, ?int $userId = null): void
    {
        $this->update([
            'statut' => self::STATUT_ANNULEE,
            'annulee_at' => now(),
            'annulee_par' => $userId ?? auth()->id(),
            'motif_annulation' => $motif,
        ]);
    }
}
