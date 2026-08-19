<?php

namespace App\Models\Vitrine;

use Illuminate\Database\Eloquent\Model;

class SlideHero extends Model
{
    protected $table = 'vitrine_slides_hero';

    protected $fillable = [
        'eyebrow', 'eyebrow_icone', 'eyebrow_couleur',
        'titre', 'sous_titre', 'cta_texte', 'cta_url',
        'stats', 'image_path', 'mockup_type',
        'ordre', 'est_actif',
    ];

    protected $casts = [
        'stats'     => 'array',
        'est_actif' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . ltrim($this->image_path, '/')) : null;
    }

    public function scopeActif($q) { return $q->where('est_actif', true); }
}
