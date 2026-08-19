<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdreModele extends Model
{
    use SoftDeletes;

    protected $table = 'finance_ordre_modeles';

    protected $fillable = [
        'code', 'libelle', 'sens',
        'numerotation_format', 'entete_titre', 'entete_soustitre',
        'phrase_intro', 'phrase_conclusion',
        'avec_mode_reglement', 'avec_pieces_justif', 'actif', 'extra_json',
    ];

    protected $casts = [
        'avec_mode_reglement' => 'boolean',
        'avec_pieces_justif'  => 'boolean',
        'actif'               => 'boolean',
        'extra_json'          => 'array',
    ];

    public const SENS_DEPENSE = 'depense';
    public const SENS_RECETTE = 'recette';

    public const SENS = [
        self::SENS_DEPENSE => 'Dépense (sortie de fonds)',
        self::SENS_RECETTE => 'Recette (entrée de fonds)',
    ];

    public function champs()
    {
        return $this->hasMany(OrdreModeleChamp::class, 'modele_id')->orderBy('ordre');
    }

    public function signataires()
    {
        return $this->hasMany(OrdreModeleSignataire::class, 'modele_id')->orderBy('ordre');
    }

    public function ordres()
    {
        return $this->hasMany(Ordre::class, 'modele_id');
    }

    public function scopeActif($q)   { return $q->where('actif', true); }
    public function scopeDepense($q) { return $q->where('sens', self::SENS_DEPENSE); }
    public function scopeRecette($q) { return $q->where('sens', self::SENS_RECETTE); }

    public function getSensLibelleAttribute(): string
    {
        return self::SENS[$this->sens] ?? '—';
    }

    public function getSensCouleurAttribute(): string
    {
        return $this->sens === self::SENS_DEPENSE ? 'danger' : 'success';
    }

    /**
     * Génère un numéro d'ordre selon `numerotation_format`.
     * Placeholders : {n:04d} (séquence zéro-paddée), {annee}, {code}
     */
    public function genererNumero(int $sequence, ?int $annee = null): string
    {
        $format = $this->numerotation_format ?: '{n:04d}';
        $annee ??= (int) date('Y');
        $format = preg_replace_callback('/\{n:(\d+)d\}/', fn($m) => str_pad((string) $sequence, (int) $m[1], '0', STR_PAD_LEFT), $format);
        $format = str_replace(['{n}', '{annee}', '{code}'], [$sequence, $annee, $this->code], $format);
        return $format;
    }
}
