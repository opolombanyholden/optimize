<?php

namespace App\Models\Vitrine;

use Illuminate\Database\Eloquent\Model;

class Capture extends Model
{
    protected $table = 'vitrine_captures';

    protected $fillable = [
        'titre', 'description', 'tag', 'tag_couleur',
        'url_affichee', 'image_path', 'mockup_type',
        'ordre', 'est_actif',
    ];

    protected $casts = ['est_actif' => 'boolean'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . ltrim($this->image_path, '/')) : null;
    }

    public function scopeActif($q) { return $q->where('est_actif', true); }
}
