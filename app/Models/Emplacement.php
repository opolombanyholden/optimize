<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Emplacement extends Model
{
    use SoftDeletes;

    public const TYPES = [
        'magasin' => 'Magasin',
        'zone' => 'Zone',
        'etagere' => 'Étagère',
        'case' => 'Case',
        'autre' => 'Autre',
    ];

    protected $fillable = [
        'code', 'libelle', 'parent_id', 'type', 'adresse',
        'responsable_id', 'description', 'actif', 'ordre',
    ];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(self::class, 'parent_id')->orderBy('ordre'); }
    public function responsable() { return $this->belongsTo(User::class, 'responsable_id'); }
    public function produits() { return $this->belongsToMany(Produit::class, 'produit_emplacements')->withPivot('quantite')->withTimestamps(); }
    public function stocks() { return $this->hasMany(ProduitEmplacement::class); }

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

    public function descendantsIds(): array
    {
        $ids = [$this->id];
        foreach ($this->enfants as $e) $ids = array_merge($ids, $e->descendantsIds());
        return $ids;
    }
}
