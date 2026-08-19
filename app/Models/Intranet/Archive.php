<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Archive extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_archives';

    protected $fillable = [
        'slug', 'titre', 'description', 'fichier', 'nom_original', 'mime_type', 'taille',
        'reference', 'nature', 'tags', 'lieu_physique', 'code_barre',
        'date_document', 'date_archivage', 'duree_conservation_mois', 'date_destruction_prevue',
        'statut', 'dossier_id', 'is_confidentiel',
        'telechargements_count', 'vues_count', 'created_by',
    ];

    protected $casts = [
        'date_document'           => 'date',
        'date_archivage'          => 'date',
        'date_destruction_prevue' => 'date',
        'is_confidentiel'         => 'boolean',
        'tags'                    => 'array',
        'taille'                  => 'integer',
        'duree_conservation_mois' => 'integer',
        'telechargements_count'   => 'integer',
        'vues_count'              => 'integer',
    ];

    protected $appends = ['fichier_url', 'taille_humaine', 'icone', 'couleur_icone', 'statut_libelle'];

    protected static function booted(): void
    {
        static::creating(function (Archive $a) {
            if (empty($a->slug)) {
                $base = Str::slug($a->titre) ?: 'archive';
                $slug = $base; $i = 2;
                while (static::where('slug', $slug)->exists()) { $slug = $base . '-' . $i++; }
                $a->slug = $slug;
            }
            if (empty($a->reference)) {
                $year = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $a->reference = "ARC-{$year}-" . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
            }
            if (empty($a->date_archivage)) {
                $a->date_archivage = now()->toDateString();
            }
            // Calcul auto date de destruction
            if ($a->duree_conservation_mois && $a->date_archivage && empty($a->date_destruction_prevue)) {
                $a->date_destruction_prevue = $a->date_archivage->copy()->addMonths($a->duree_conservation_mois);
            }
        });
    }

    // Relations
    public function dossier() { return $this->belongsTo(ArchiveDossier::class, 'dossier_id'); }
    public function auteur() { return $this->belongsTo(User::class, 'created_by'); }

    // Accessors
    public function getFichierUrlAttribute(): ?string
    {
        if (! $this->fichier) return null;
        return asset('storage/' . ltrim($this->fichier, '/'));
    }

    public function getTailleHumaineAttribute(): string
    {
        $b = $this->taille ?? 0;
        if ($b >= 1073741824) return number_format($b / 1073741824, 2) . ' Go';
        if ($b >= 1048576) return number_format($b / 1048576, 2) . ' Mo';
        if ($b >= 1024) return number_format($b / 1024, 2) . ' Ko';
        return $b . ' o';
    }

    public function getIconeAttribute(): string
    {
        return match (true) {
            str_starts_with($this->mime_type ?? '', 'image/') => 'fa-image',
            in_array($this->mime_type, ['application/pdf']) => 'fa-file-pdf',
            str_contains($this->mime_type ?? '', 'word') => 'fa-file-word',
            str_contains($this->mime_type ?? '', 'spreadsheet') => 'fa-file-excel',
            default => 'fa-box-archive',
        };
    }

    public function getCouleurIconeAttribute(): string
    {
        return match (true) {
            in_array($this->mime_type, ['application/pdf']) => '#DC2626',
            str_contains($this->mime_type ?? '', 'word') => '#2563EB',
            str_contains($this->mime_type ?? '', 'spreadsheet') => '#16A34A',
            default => '#64748B',
        };
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'actif' => 'Actif', 'semi_actif' => 'Semi-actif',
            'inactif' => 'Inactif', 'a_detruire' => 'À détruire',
            default => ucfirst((string) $this->statut),
        };
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'actif' => '#16A34A', 'semi_actif' => '#F59E0B',
            'inactif' => '#94A3B8', 'a_detruire' => '#DC2626',
            default => '#64748B',
        };
    }

    // Scopes
    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('titre', 'like', "%{$terme}%")
              ->orWhere('reference', 'like', "%{$terme}%")
              ->orWhere('description', 'like', "%{$terme}%")
              ->orWhere('nature', 'like', "%{$terme}%")
              ->orWhere('code_barre', 'like', "%{$terme}%");
        });
    }

    public function scopeADetruire(Builder $q): Builder
    {
        return $q->whereNotNull('date_destruction_prevue')
                 ->where('date_destruction_prevue', '<=', now());
    }
}
