<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MediaAlbum extends Model
{
    use SoftDeletes;

    protected $table = 'intranet_media_albums';

    protected $fillable = [
        'nom', 'slug', 'description', 'couverture', 'couleur', 'icone',
        'parent_id', 'is_public', 'created_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected $appends = ['couverture_url'];

    protected static function booted(): void
    {
        static::creating(function (MediaAlbum $a) {
            if (empty($a->slug)) {
                $base = Str::slug($a->nom) ?: 'album';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $a->slug = $slug;
            }
        });
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent()
    {
        return $this->belongsTo(MediaAlbum::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(MediaAlbum::class, 'parent_id')->orderBy('nom');
    }

    public function medias()
    {
        return $this->hasMany(Media::class, 'album_id')->orderByDesc('created_at');
    }

    public function getCouvertureUrlAttribute(): ?string
    {
        if ($this->couverture) return asset('storage/' . ltrim($this->couverture, '/'));
        $premier = $this->medias()->where('type', 'image')->first();
        return $premier?->fichier_url;
    }

    public function getCheminCompletAttribute(): array
    {
        $path = [];
        $current = $this;
        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent;
        }
        return $path;
    }

    public function scopeRacine(Builder $q): Builder
    {
        return $q->whereNull('parent_id');
    }
}
