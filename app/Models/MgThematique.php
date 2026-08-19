<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MgThematique extends Model
{
    protected $table = 'mg_thematiques';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['actif' => 'boolean'];
    }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(self::class, 'parent_id')->orderBy('ordre'); }
    public function dysfonctionnements() { return $this->hasMany(Dysfonctionnement::class, 'thematique_id'); }
    public function interventions() { return $this->hasMany(Intervention::class, 'thematique_id'); }

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
