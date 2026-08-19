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

class Courrier extends Model
{
    use HasPublication, HasLikes, HasCommentaires, HasVues, HasPiecesJointes, SoftDeletes;

    protected $table = 'intranet_courriers';

    protected $fillable = [
        'type', 'slug', 'objet', 'extrait', 'reference', 'contenu',
        'expediteur', 'expediteur_email', 'expediteur_organisation',
        'destinataire', 'destinataire_email',
        'service_destinataire_id', 'service_expediteur_id',
        'date_reception', 'date_expedition',
        'assigne_a', 'assigne_le', 'assigne_par',
        'traite_le', 'traite_par', 'echeance_traitement',
        'accuse_reception', 'accuse_reception_le',
        'accuse_reception_methode', 'accuse_reception_scan',
        'confidentiel', 'urgent', 'priorite', 'statut',
        'media_principal', 'media_principal_type', 'vues_count',
        'created_by',
    ];

    protected $casts = [
        'date_reception'      => 'date',
        'date_expedition'     => 'date',
        'echeance_traitement' => 'date',
        'assigne_le'          => 'datetime',
        'traite_le'           => 'datetime',
        'accuse_reception_le' => 'datetime',
        'accuse_reception'    => 'boolean',
        'confidentiel'        => 'boolean',
        'urgent'              => 'boolean',
        'vues_count'          => 'integer',
    ];

    protected $appends = ['media_url', 'accuse_reception_scan_url', 'est_en_retard'];

    // ── Boot ───────────────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Courrier $c) {
            if (empty($c->slug)) {
                $c->slug = static::genererSlugUnique($c->objet);
            }
            if (empty($c->reference)) {
                $c->reference = static::genererReference($c->type);
            }
        });
    }

    protected static function genererSlugUnique(string $base): string
    {
        $slug = Str::slug($base) ?: 'courrier';
        $original = $slug;
        $i = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    protected static function genererReference(string $type): string
    {
        $year = now()->year;
        $prefix = match ($type) {
            'sortant' => "COU-{$year}-S",
            'interne' => "COU-{$year}-I",
            default   => "COU-{$year}-",
        };
        $count = static::where('type', $type)
            ->whereYear('created_at', $year)
            ->count() + 1;
        return $prefix . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    // ── Relations ──────────────────────────────────────────
    public function auteur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigne()
    {
        return $this->belongsTo(User::class, 'assigne_a');
    }

    public function assignePar()
    {
        return $this->belongsTo(User::class, 'assigne_par');
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function serviceDestinataire()
    {
        return $this->belongsTo(\App\Models\Intranet\Service::class, 'service_destinataire_id');
    }

    public function serviceExpediteur()
    {
        return $this->belongsTo(\App\Models\Intranet\Service::class, 'service_expediteur_id');
    }

    public function traitements()
    {
        return $this->hasMany(CourrierTraitement::class, 'courrier_id')->orderByDesc('created_at');
    }

    // ── Accessors ──────────────────────────────────────────
    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_principal) return null;
        return asset('storage/' . ltrim($this->media_principal, '/'));
    }

    public function getAccuseReceptionScanUrlAttribute(): ?string
    {
        if (! $this->accuse_reception_scan) return null;
        return asset('storage/' . ltrim($this->accuse_reception_scan, '/'));
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->echeance_traitement
            && $this->echeance_traitement->lt(now())
            && ! in_array($this->statut, ['traite', 'archive']);
    }

    public function getEstTraiteAttribute(): bool
    {
        return in_array($this->statut, ['traite', 'archive']);
    }

    public function getStatutLibelleAttribute(): string
    {
        return match ($this->statut) {
            'recu'          => 'Reçu',
            'en_traitement' => 'En traitement',
            'traite'        => 'Traité',
            'archive'       => 'Archivé',
            'envoye'        => 'Envoyé',
            'expedie'       => 'Expédié',
            'brouillon'     => 'Brouillon',
            default         => ucfirst((string) $this->statut),
        };
    }

    public function getStatutCouleurAttribute(): string
    {
        return match ($this->statut) {
            'recu'          => '#7C3AED',
            'en_traitement' => '#F59E0B',
            'traite'        => '#16A34A',
            'archive'       => '#64748B',
            'envoye', 'expedie' => '#0891B2',
            'brouillon'     => '#94A3B8',
            default         => '#94A3B8',
        };
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeEnAttente(Builder $q): Builder
    {
        return $q->whereIn('statut', ['recu', 'en_traitement']);
    }

    public function scopeAssignesAUser(Builder $q, int $userId): Builder
    {
        return $q->where('assigne_a', $userId)
                 ->whereNotIn('statut', ['traite', 'archive']);
    }

    public function scopeRecherche(Builder $q, ?string $terme): Builder
    {
        if (! $terme) return $q;
        return $q->where(function ($w) use ($terme) {
            $w->where('objet', 'like', "%{$terme}%")
              ->orWhere('reference', 'like', "%{$terme}%")
              ->orWhere('expediteur', 'like', "%{$terme}%")
              ->orWhere('destinataire', 'like', "%{$terme}%")
              ->orWhere('contenu', 'like', "%{$terme}%");
        });
    }

    // ── Méthodes de workflow ───────────────────────────────
    public function ajouterTraitement(string $action, ?string $commentaire = null, array $meta = []): CourrierTraitement
    {
        return $this->traitements()->create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'commentaire' => $commentaire,
            'meta'        => $meta,
        ]);
    }

    public function assignerA(User $user, ?string $commentaire = null): void
    {
        $ancien = $this->assigne_a;
        $this->update([
            'assigne_a'    => $user->id,
            'assigne_le'   => now(),
            'assigne_par'  => auth()->id(),
            'statut'       => $this->statut === 'recu' ? 'en_traitement' : $this->statut,
        ]);
        $this->ajouterTraitement(
            $ancien ? 'reassigne' : 'assigne',
            $commentaire,
            ['ancien_assigne' => $ancien, 'nouveau_assigne' => $user->id]
        );
    }

    public function marquerTraite(?string $commentaire = null): void
    {
        $this->update([
            'statut'     => 'traite',
            'traite_le'  => now(),
            'traite_par' => auth()->id(),
        ]);
        $this->ajouterTraitement('traite', $commentaire);
    }

    public function enregistrerAccuseReception(?string $methode = null, ?string $scanPath = null): void
    {
        $this->update([
            'accuse_reception'         => true,
            'accuse_reception_le'      => now(),
            'accuse_reception_methode' => $methode,
            'accuse_reception_scan'    => $scanPath ?? $this->accuse_reception_scan,
        ]);
        $this->ajouterTraitement('accuse_reception', "Méthode : {$methode}");
    }
}
