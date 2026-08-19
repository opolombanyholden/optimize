<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasProtection;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tache extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, HasProtection, SoftDeletes;

    protected $table = 'intranet_taches';

    protected $fillable = [
        'titre', 'slug', 'description', 'resume', 'besoins',
        'projet_id', 'phase_id', 'jalon_id', 'objectif_id', 'priorite_id', 'statut_id',
        'responsable_id', 'created_by',
        'date_debut', 'date_fin', 'date_debut_reelle', 'date_fin_reelle',
        'heures_estimees', 'heures_reelles', 'temps_reel_heures',
        'avancement', 'ordre', 'est_jalon',
        'cout_execution', 'devise_cout', 'ponderation',
        'statut_validation', 'soumis_le', 'valide_le', 'motif_rejet',
        'note_evaluation', 'appreciation', 'evalue_par', 'evalue_le',
        'media_principal', 'media_principal_type', 'vues_count',
        'deadline_alerted_at',
    ];

    protected $casts = [
        'date_debut'        => 'date',
        'date_fin'          => 'date',
        'date_debut_reelle' => 'date',
        'date_fin_reelle'   => 'date',
        'est_jalon'         => 'boolean',
        'avancement'        => 'integer',
        'heures_estimees'   => 'decimal:2',
        'heures_reelles'    => 'decimal:2',
        'temps_reel_heures' => 'decimal:2',
        'cout_execution'    => 'decimal:2',
        'ponderation'       => 'decimal:2',
        'note_evaluation'   => 'integer',
        'soumis_le'         => 'datetime',
        'valide_le'         => 'datetime',
        'evalue_le'         => 'datetime',
        'vues_count'        => 'integer',
        'deadline_alerted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tache $t) {
            if (empty($t->slug)) {
                $base = Str::slug($t->titre) ?: 'tache';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->exists()) { $slug = $base . '-' . $i++; }
                $t->slug = $slug;
            }
        });
    }

    // ── Relations ────────────────────────────────────────
    public function statut()      { return $this->belongsTo(Statut::class, 'statut_id'); }
    public function priorite()    { return $this->belongsTo(Priorite::class, 'priorite_id'); }
    public function projet()      { return $this->belongsTo(Projet::class, 'projet_id'); }
    public function objectif()    { return $this->belongsTo(Objectif::class, 'objectif_id'); }
    public function phase()       { return $this->belongsTo(ProjetPhase::class, 'phase_id'); }
    public function jalon()       { return $this->belongsTo(Jalon::class, 'jalon_id'); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function auteur()      { return $this->belongsTo(User::class, 'created_by'); }
    public function evaluateur()  { return $this->belongsTo(User::class, 'evalue_par'); }
    public function assignes()    { return $this->belongsToMany(User::class, 'intranet_tache_user'); }
    public function activites()   { return $this->hasMany(Activite::class, 'tache_id')->orderBy('ordre'); }
    public function checklist()   { return $this->hasMany(TacheChecklist::class, 'tache_id')->orderBy('ordre'); }
    public function livrables()   { return $this->hasMany(ProjetLivrable::class, 'tache_id'); }
    public function couts()       { return $this->hasMany(ProjetCout::class, 'tache_id'); }
    public function feuillesTemps() { return $this->hasMany(FeuilleTemps::class, 'tache_id'); }
    public function dependancesSortantes() { return $this->morphMany(Dependance::class, 'source'); }
    public function dependancesEntrantes() { return $this->morphMany(Dependance::class, 'cible'); }

    // Valideurs (users ou groupes)
    public function valideurs()
    {
        return $this->hasMany(TacheValideur::class, 'tache_id');
    }

    public function validateursUsers()
    {
        return $this->valideurs()->where('valideur_type', 'user');
    }

    public function validateursGroupes()
    {
        return $this->valideurs()->where('valideur_type', 'groupe');
    }

    // Historique
    public function historique()
    {
        return $this->hasMany(TacheHistorique::class, 'tache_id')->orderByDesc('created_at');
    }

    // ── Accessors ────────────────────────────────────────
    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getAvancementActivitesAttribute(): int
    {
        $acts = $this->activites;
        if ($acts->isEmpty()) return $this->avancement;
        return (int) $acts->avg('avancement');
    }

    /**
     * Coût total estimé : cout_execution + somme des lignes de coût estimées.
     */
    public function getCoutTotalEstimeAttribute(): float
    {
        return (float) ($this->cout_execution ?? 0) + (float) $this->couts->sum('montant_estime');
    }

    /**
     * Coût total réel : somme des lignes de coût réelles.
     */
    public function getCoutTotalReelAttribute(): float
    {
        return (float) $this->couts->sum('montant_reel');
    }

    public function getChecklistProgressionAttribute(): array
    {
        $items = $this->checklist;
        $total = $items->count();
        $done  = $items->where('complete', true)->count();
        return ['total' => $total, 'done' => $done, 'pct' => $total ? round($done / $total * 100) : 0];
    }

    protected function determinerVerrouillage(): bool
    {
        return $this->activites()->exists()
            || $this->commentaires()->exists()
            || $this->feuillesTemps()->exists()
            || $this->couts()->exists()
            || $this->avancement > 0;
    }

    public function estEnRetard(): bool
    {
        return ! in_array($this->statut?->libelle, ['Terminé', 'Annulé'])
            && $this->date_fin?->isPast();
    }

    public function getEstTermineeAttribute(): bool
    {
        return $this->statut?->libelle === 'Terminé' || $this->avancement >= 100;
    }

    public function getPrioriteCouleurAttribute(): string
    {
        return $this->priorite?->couleur ?? '#64748B';
    }

    public function getStatutCouleurAttribute(): string
    {
        return $this->statut?->couleur ?? '#64748B';
    }

    public function getValidationLibelleAttribute(): string
    {
        return match ($this->statut_validation) {
            'non_soumis' => 'Non soumis',
            'soumis'     => 'Soumis',
            'approuve'   => 'Approuvé',
            'rejete'     => 'Rejeté',
            'revisions'  => 'Révisions demandées',
            default      => '—',
        };
    }

    public function getValidationCouleurAttribute(): string
    {
        return match ($this->statut_validation) {
            'soumis'    => '#0891B2',
            'approuve'  => '#16A34A',
            'rejete'    => '#DC2626',
            'revisions' => '#F59E0B',
            default     => '#94A3B8',
        };
    }

    /**
     * Vérifie si l'utilisateur courant est valideur de cette tâche.
     */
    public function estValideur(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) return false;

        // Valideur direct
        if ($this->valideurs()->where('valideur_type', 'user')->where('valideur_id', $user->id)->exists()) {
            return true;
        }

        // Membre d'un groupe valideur
        $groupeIds = $this->valideurs()->where('valideur_type', 'groupe')->pluck('valideur_id');
        if ($groupeIds->isNotEmpty()) {
            return $user->groupesIntranet()->whereIn('intranet_groupes.id', $groupeIds)->exists();
        }

        return false;
    }

    // ── Workflow ─────────────────────────────────────────
    public function ajouterHistorique(string $action, ?string $commentaire = null, ?int $note = null, array $meta = []): TacheHistorique
    {
        return $this->historique()->create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'commentaire' => $commentaire,
            'note'        => $note,
            'meta'        => $meta,
        ]);
    }

    public function soumettrePourValidation(): void
    {
        $this->update([
            'statut_validation' => 'soumis',
            'soumis_le'         => now(),
        ]);
        $this->ajouterHistorique('soumis');
    }

    public function approuverTache(?string $commentaire = null): void
    {
        $this->update([
            'statut_validation' => 'approuve',
            'valide_le'         => now(),
            'motif_rejet'       => null,
        ]);
        $this->ajouterHistorique('approuve', $commentaire);
    }

    public function rejeterTache(string $motif): void
    {
        $this->update([
            'statut_validation' => 'rejete',
            'valide_le'         => now(),
            'motif_rejet'       => $motif,
        ]);
        $this->ajouterHistorique('rejete', $motif);
    }

    public function demanderRevisionsTache(string $commentaire): void
    {
        $this->update([
            'statut_validation' => 'revisions',
            'motif_rejet'       => $commentaire,
        ]);
        $this->ajouterHistorique('revisions_demandees', $commentaire);
    }

    public function evaluerTache(int $note, ?string $appreciation = null): void
    {
        $this->update([
            'note_evaluation' => $note,
            'appreciation'    => $appreciation,
            'evalue_par'      => auth()->id(),
            'evalue_le'       => now(),
        ]);
        $this->ajouterHistorique('evalue', $appreciation, $note);
    }

    // ── Scopes ───────────────────────────────────────────
    public function scopeAssigneesA(Builder $q, int $userId): Builder
    {
        return $q->where(function ($w) use ($userId) {
            $w->where('responsable_id', $userId)
              ->orWhereHas('assignes', fn($sub) => $sub->where('users.id', $userId));
        });
    }

    public function scopeEnCours(Builder $q): Builder
    {
        return $q->whereHas('statut', fn($s) => $s->whereIn('libelle', ['En cours', 'Non démarré']));
    }

    public function scopeEnRetard(Builder $q): Builder
    {
        return $q->where('date_fin', '<', now())
                 ->whereHas('statut', fn($s) => $s->whereNotIn('libelle', ['Terminé', 'Annulé']));
    }

    public function scopeAValider(Builder $q, int $userId): Builder
    {
        return $q->where('statut_validation', 'soumis')
                 ->whereHas('valideurs', function ($w) use ($userId) {
                     $w->where(function ($sub) use ($userId) {
                         $sub->where('valideur_type', 'user')->where('valideur_id', $userId);
                     });
                 });
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%")
              ->orWhere('resume', 'like', "%{$terme}%");
        });
    }
}
