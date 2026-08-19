<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WikiCategorie extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_wiki_categories';
    protected $fillable = ['nom', 'slug', 'description', 'icone', 'couleur', 'parent_id', 'ordre'];

    public function parent()   { return $this->belongsTo(WikiCategorie::class, 'parent_id'); }
    public function enfants()  { return $this->hasMany(WikiCategorie::class, 'parent_id')->orderBy('ordre'); }
    public function articles() { return $this->hasMany(WikiArticle::class, 'categorie_id')->orderBy('ordre'); }

    public function getCheminCompletAttribute(): array
    {
        $path = [];
        $current = $this;
        while ($current) { array_unshift($path, $current); $current = $current->parent; }
        return $path;
    }
}
