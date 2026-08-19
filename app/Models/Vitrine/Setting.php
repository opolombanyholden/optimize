<?php

namespace App\Models\Vitrine;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'vitrine_settings';

    protected $fillable = ['cle', 'valeur', 'libelle', 'groupe', 'type', 'ordre'];

    /**
     * Helper rapide pour récupérer une valeur par clé.
     */
    public static function get(string $cle, ?string $default = null): ?string
    {
        return Cache::remember("vitrine_setting_{$cle}", 600, function () use ($cle, $default) {
            return self::where('cle', $cle)->value('valeur') ?? $default;
        });
    }

    /**
     * Récupérer toutes les valeurs sous forme de map [cle => valeur].
     */
    public static function getAll(): \Illuminate\Support\Collection
    {
        return Cache::remember('vitrine_settings_all', 600, function () {
            return self::query()->orderBy('groupe')->orderBy('ordre')->get()
                ->mapWithKeys(fn($s) => [$s->cle => $s->valeur]);
        });
    }

    protected static function booted(): void
    {
        static::saved(fn($s) => self::flushCache($s->cle));
        static::deleted(fn($s) => self::flushCache($s->cle));
    }

    private static function flushCache(string $cle): void
    {
        Cache::forget("vitrine_setting_{$cle}");
        Cache::forget('vitrine_settings_all');
    }
}
