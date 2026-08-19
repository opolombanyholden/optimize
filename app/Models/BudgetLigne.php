<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetLigne extends Model
{
    protected $table = 'budget_lignes';

    protected $fillable = [
        'id_budgetligne', 'dateeffect', 'id_exercicebudgetaire',
        'exercice', 'id_famillecodeanalytique', 'id_codeanalytique',
        'codecompte', 'budgetligne', 'commentaire',
        'attribut1', 'attribut2', 'attribut3',
        'isvalide', 'id_user', 'effacer',
        'dotation_etat', 'fonds_propres', 'reports_budgetaire',
        'reports_tresorerie', 'transfert', 'engagement',
        'retiree_de_planification',
    ];

    protected $casts = [
        'retiree_de_planification' => 'boolean',
    ];

    // ─── Scopes ─────────────────────────────────────────
    public function scopeNonRetirees($q)  { return $q->where('retiree_de_planification', false); }
    public function scopeRetirees($q)     { return $q->where('retiree_de_planification', true); }

    /**
     * Une ligne ne peut être retirée que si elle n'a aucun engagement.
     */
    public function peutEtreRetiree(): bool
    {
        return (float) ($this->engagement ?? 0) <= 0;
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class, 'id_exercicebudgetaire');
    }

    public function titre()
    {
        return $this->belongsTo(Titre::class, 'id_famillecodeanalytique');
    }

    public function ligne()
    {
        return $this->belongsTo(Ligne::class, 'id_codeanalytique');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function getBudgetTotalAttribute(): float
    {
        return ($this->dotation_etat ?? 0) + ($this->fonds_propres ?? 0)
             + ($this->reports_budgetaire ?? 0) + ($this->reports_tresorerie ?? 0)
             + ($this->transfert ?? 0);
    }

    public function getSoldeDisponibleAttribute(): float
    {
        return $this->budget_total - ($this->engagement ?? 0);
    }

    /**
     * Somme des recettes déjà enregistrées (ordres exécutés en sens=recette)
     * sur cette ligne budgétaire. Sert de "solde précédent" pour un ordre de recette.
     */
    public function sommeRecettesEnregistrees(): float
    {
        return (float) \App\Models\Finance\Ordre::where('budget_ligne_id', $this->id)
            ->where('sens', 'recette')
            ->where('statut', \App\Models\Finance\Ordre::STATUT_EXECUTE)
            ->sum('montant');
    }
}
