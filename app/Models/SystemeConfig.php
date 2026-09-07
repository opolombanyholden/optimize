<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemeConfig extends Model
{
    protected $table = 'systeme_config';

    protected $fillable = ['cle', 'valeur', 'type', 'categorie', 'libelle', 'description', 'editable', 'ordre'];

    protected $casts = ['editable' => 'boolean', 'ordre' => 'integer'];

    /**
     * Cache clé statique — vidé lors de tout update.
     */
    protected const CACHE_KEY = 'systeme_config_all';

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget(self::CACHE_KEY));
        static::deleted(fn() => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Récupère toutes les configs (avec cache).
     */
    public static function all_cached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()->get()->keyBy('cle')->map(fn($c) => $c->valeur_castee)->toArray();
        });
    }

    /**
     * Helper : lit une clé avec fallback.
     */
    public static function get(string $cle, mixed $default = null): mixed
    {
        return self::all_cached()[$cle] ?? $default;
    }

    /**
     * Helper : écrit une clé.
     */
    public static function set(string $cle, mixed $valeur): void
    {
        $row = self::where('cle', $cle)->first();
        if ($row) $row->update(['valeur' => is_scalar($valeur) ? (string) $valeur : json_encode($valeur)]);
    }

    /**
     * Accessor : renvoie la valeur convertie selon le type déclaré.
     */
    public function getValeurCasteeAttribute(): mixed
    {
        $v = $this->attributes['valeur'] ?? null;
        return match ($this->type) {
            'int'   => is_numeric($v) ? (int) $v : null,
            'bool'  => in_array((string) $v, ['1', 'true', 'on', 'yes'], true),
            'json'  => $v ? json_decode($v, true) : null,
            default => $v,
        };
    }

    public function scopeParCategorie($q, string $categorie)
    {
        return $q->where('categorie', $categorie)->orderBy('ordre')->orderBy('libelle');
    }
}
