<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ArchiveDossier extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_archive_dossiers';

    protected $fillable = [
        'nom', 'slug', 'description', 'parent_id',
        'couleur', 'icone', 'couverture', 'created_by',
    ];

    protected $appends = ['couverture_url'];

    protected static function booted(): void
    {
        static::creating(function (ArchiveDossier $d) {
            if (empty($d->slug)) {
                $base = Str::slug($d->nom) ?: 'dossier';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->exists()) { $slug = $base . '-' . $i++; }
                $d->slug = $slug;
            }
        });
    }

    public function parent() { return $this->belongsTo(ArchiveDossier::class, 'parent_id'); }
    public function enfants() { return $this->hasMany(ArchiveDossier::class, 'parent_id')->orderBy('nom'); }
    public function archives() { return $this->hasMany(Archive::class, 'dossier_id')->orderByDesc('date_archivage'); }
    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }

    public function getCouvertureUrlAttribute(): ?string
    {
        if ($this->couverture) return asset('storage/' . ltrim($this->couverture, '/'));
        return null;
    }

    public function getCheminCompletAttribute(): array
    {
        $path = [];
        $current = $this;
        while ($current) { array_unshift($path, $current); $current = $current->parent; }
        return $path;
    }

    public function scopeRacine(Builder $q): Builder { return $q->whereNull('parent_id'); }
}
