<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasProtection;
use App\Traits\Intranet\HasValidation;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projet extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, HasValidation, HasProtection, SoftDeletes;

    protected $table = 'intranet_projets';

    protected $fillable = [
        'nom', 'code_projet', 'categorie', 'description', 'objectifs',
        'perimetre_inclus', 'perimetre_exclus', 'hypotheses', 'contraintes',
        'budget_approuve', 'devise', 'priorite_id', 'statut_id', 'objectif_id',
        'sponsor_id', 'chef_projet_id', 'created_by',
        'date_debut', 'date_fin', 'avancement',
        'statut_cloture', 'justification_cloture', 'soumis_le', 'valide_le', 'motif_rejet',
        'organisation_id', 'contact_id', 'opportunite_id',
    ];

    protected $casts = [
        'date_debut'      => 'date',
        'date_fin'        => 'date',
        'budget_approuve' => 'decimal:2',
        'avancement'      => 'integer',
        'soumis_le'       => 'datetime',
        'valide_le'       => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────
    public function statut()       { return $this->belongsTo(Statut::class, 'statut_id'); }
    public function objectif()     { return $this->belongsTo(Objectif::class, 'objectif_id'); }
    public function organisation() { return $this->belongsTo(ContactOrganisation::class, 'organisation_id'); }
    public function contact()      { return $this->belongsTo(Contact::class, 'contact_id'); }
    public function opportunite()  { return $this->belongsTo(Opportunite::class, 'opportunite_id'); }

    /**
     * Durée estimée en jours (depuis date_debut → date_fin planifiées).
     */
    public function getDureeEstimeeAttribute(): ?int
    {
        if (! $this->date_debut || ! $this->date_fin) return null;
        return (int) $this->date_debut->diffInDays($this->date_fin, false);
    }

    /**
     * Durée réelle en jours : si projet terminé/clôturé, depuis date_debut jusqu'à valide_le.
     * Sinon, depuis date_debut jusqu'à aujourd'hui.
     */
    public function getDureeReelleAttribute(): ?int
    {
        if (! $this->date_debut) return null;
        $fin = $this->valide_le ?? now();
        return (int) $this->date_debut->diffInDays($fin, false);
    }
    public function priorite()     { return $this->belongsTo(Priorite::class, 'priorite_id'); }
    public function auteur()       { return $this->belongsTo(User::class, 'created_by'); }
    public function sponsor()      { return $this->belongsTo(User::class, 'sponsor_id'); }
    public function chefProjet()   { return $this->belongsTo(User::class, 'chef_projet_id'); }
    public function membres()      { return $this->belongsToMany(User::class, 'intranet_projet_user'); }
    public function phases()       { return $this->hasMany(ProjetPhase::class, 'projet_id')->orderBy('ordre'); }
    public function taches()       { return $this->hasMany(Tache::class, 'projet_id'); }
    public function activites()    { return $this->hasManyThrough(Activite::class, Tache::class, 'projet_id', 'tache_id'); }
    public function jalons()       { return $this->hasMany(Jalon::class, 'projet_id')->orderBy('date_prevue'); }
    public function ressources()   { return $this->hasMany(ProjetRessource::class, 'projet_id'); }
    public function feuillesTemps(){ return $this->hasMany(FeuilleTemps::class, 'projet_id'); }
    public function couts()        { return $this->hasMany(ProjetCout::class, 'projet_id'); }
    public function evm()          { return $this->hasMany(ProjetEvm::class, 'projet_id')->orderBy('date_mesure'); }
    public function dernierEvm()   { return $this->hasOne(ProjetEvm::class, 'projet_id')->latest('date_mesure'); }
    public function risques()      { return $this->hasMany(ProjetRisque::class, 'projet_id'); }
    public function problemes()    { return $this->hasMany(ProjetProbleme::class, 'projet_id'); }
    public function changements()  { return $this->hasMany(ProjetChangement::class, 'projet_id'); }
    public function livrables()    { return $this->hasMany(ProjetLivrable::class, 'projet_id'); }
    public function partiesPrenantes() { return $this->hasMany(ProjetPartiePrenante::class, 'projet_id'); }
    public function lecons()       { return $this->hasMany(ProjetLecon::class, 'projet_id'); }

    // ── Accessors ────────────────────────────────────────
    public function getStatutCouleurAttribute(): string { return $this->statut?->couleur ?? '#64748B'; }
    public function getPrioriteCouleurAttribute(): string { return $this->priorite?->couleur ?? '#64748B'; }

    /**
     * Coût total estimé en cascade :
     * Si des phases existent → somme des coûts estimés des phases + coûts directs (sans phase ni tâche).
     * Sinon → somme de tous les ProjetCout estimés.
     */
    public function getCoutTotalEstimeAttribute(): float
    {
        $phases = $this->phases;
        if ($phases->isNotEmpty()) {
            $coutPhases = $phases->sum(fn($p) => $p->cout_estime);
            // Coûts directs du projet (non liés à une phase ni une tâche)
            $coutDirect = (float) $this->couts()->whereNull('phase_id')->whereNull('tache_id')->sum('montant_estime');
            return $coutPhases + $coutDirect;
        }
        return (float) $this->couts()->sum('montant_estime');
    }

    /**
     * Budget consommé (coût réel) en cascade :
     * Si des phases existent → somme des coûts réels des phases + coûts directs.
     * Sinon → somme de tous les ProjetCout réels.
     */
    public function getBudgetConsommeAttribute(): float
    {
        $phases = $this->phases;
        if ($phases->isNotEmpty()) {
            $coutPhases = $phases->sum(fn($p) => $p->cout_reel);
            $coutDirect = (float) $this->couts()->whereNull('phase_id')->whereNull('tache_id')->sum('montant_reel');
            return $coutPhases + $coutDirect;
        }
        return (float) $this->couts()->sum('montant_reel');
    }

    public function getBudgetRestantAttribute(): float
    {
        return (float) ($this->budget_approuve ?? 0) - $this->budget_consomme;
    }

    /**
     * Écart budgétaire global (réel - estimé).
     */
    public function getEcartBudgetAttribute(): float
    {
        return $this->budget_consomme - $this->cout_total_estime;
    }

    public function getTotalHeuresEstimeesAttribute(): float
    {
        return (float) $this->taches()->sum('heures_estimees');
    }

    public function getTotalHeuresReellesAttribute(): float
    {
        return (float) $this->feuillesTemps()->where('statut', 'approuve')->sum('heures');
    }

    public function getRisquesCritiquesAttribute(): int
    {
        return $this->risques()
            ->whereIn('statut', ['identifie', 'analyse', 'traite'])
            ->whereRaw('probabilite * impact >= 15')
            ->count();
    }

    /**
     * Avancement réel calculé en cascade :
     * 1. Si des phases existent avec pondération → moyenne pondérée des phases
     * 2. Sinon si des phases existent → moyenne simple des phases
     * 3. Sinon si des tâches existent avec pondération → moyenne pondérée des tâches
     * 4. Sinon → moyenne simple des tâches
     * 5. Sinon → valeur manuelle du champ avancement
     */
    public function getAvancementRealAttribute(): int
    {
        // Priorité 1 : calcul par phases
        $phases = $this->phases;
        if ($phases->isNotEmpty()) {
            $totalPond = $phases->sum('ponderation');
            if ($totalPond > 0) {
                $somme = $phases->sum(fn($p) => $p->avancement_real * ($p->ponderation ?? 0));
                return (int) round($somme / $totalPond);
            }
            return (int) round($phases->avg(fn($p) => $p->avancement_real));
        }

        // Priorité 2 : calcul par tâches directes
        $taches = $this->taches;
        if ($taches->isNotEmpty()) {
            $totalPond = $taches->sum('ponderation');
            if ($totalPond > 0) {
                $somme = $taches->sum(fn($t) => $t->avancement * ($t->ponderation ?? 0));
                return (int) round($somme / $totalPond);
            }
            return (int) round($taches->avg('avancement'));
        }

        return $this->avancement ?? 0;
    }

    /**
     * Recalcule et sauvegarde l'avancement du projet.
     */
    public function recalculerAvancement(): void
    {
        $this->update(['avancement' => $this->avancement_real]);
    }

    protected function determinerVerrouillage(): bool
    {
        return $this->phases()->exists()
            || $this->taches()->exists()
            || $this->jalons()->exists()
            || $this->couts()->exists()
            || $this->risques()->exists();
    }

    public function estEnRetard(): bool
    {
        return ! in_array($this->statut?->libelle, ['Terminé', 'Annulé'])
            && $this->date_fin?->isPast();
    }

    // ── Scopes ───────────────────────────────────────────
    public function scopeEnCours(Builder $q): Builder
    {
        return $q->whereHas('statut', fn($s) => $s->whereIn('libelle', ['En cours', 'Non démarré']));
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('nom', 'like', "%{$terme}%")
              ->orWhere('code_projet', 'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%")
              ->orWhere('categorie', 'like', "%{$terme}%");
        });
    }
}
