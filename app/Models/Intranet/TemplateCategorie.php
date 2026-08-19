<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemplateCategorie extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_template_categories';

    protected $fillable = ['nom', 'slug', 'icone', 'couleur', 'description', 'ordre'];

    public function templates()
    {
        return $this->hasMany(Template::class, 'categorie_id');
    }
}
