<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilleArticle extends Model
{
    use SoftDeletes;

    protected $table = 'familles_articles';

    protected $fillable = [
        'libelle', 'code', 'parent_id', 'ordre', 'description', 'actif',
    ];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(self::class, 'parent_id')->orderBy('ordre'); }
    public function articles() { return $this->hasMany(Produit::class, 'famille_id'); }

    /**
     * Chemin complet de la famille (ex: "Fournitures › Papeterie › Papier").
     */
    public function getCheminAttribute(): string
    {
        $parts = [$this->libelle];
        $node = $this->parent;
        while ($node) {
            array_unshift($parts, $node->libelle);
            $node = $node->parent;
        }
        return implode(' › ', $parts);
    }

    /**
     * Tous les descendants récursivement.
     */
    public function descendantsIds(): array
    {
        $ids = [$this->id];
        foreach ($this->enfants as $e) $ids = array_merge($ids, $e->descendantsIds());
        return $ids;
    }
}
