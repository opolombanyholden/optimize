<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduitMouvement extends Model
{
    protected $table = 'produit_mouvements';

    protected $fillable = [
        'produit_id', 'type', 'quantite', 'stock_apres',
        'emplacement_id', 'emplacement_source_id',
        'reference', 'motif', 'source_type', 'source_id', 'user_id',
    ];

    protected $casts = [
        'quantite'    => 'decimal:3',
        'stock_apres' => 'decimal:3',
    ];

    public const TYPE_ENTREE     = 'entree';
    public const TYPE_SORTIE     = 'sortie';
    public const TYPE_AJUSTEMENT = 'ajustement';
    public const TYPE_TRANSFERT  = 'transfert';

    public const TYPES = [
        self::TYPE_ENTREE     => 'Entrée (livraison)',
        self::TYPE_SORTIE     => 'Sortie',
        self::TYPE_AJUSTEMENT => 'Ajustement inventaire',
        self::TYPE_TRANSFERT  => 'Transfert entre emplacements',
    ];

    public function produit() { return $this->belongsTo(Produit::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function emplacement() { return $this->belongsTo(Emplacement::class, 'emplacement_id'); }
    public function emplacementSource() { return $this->belongsTo(Emplacement::class, 'emplacement_source_id'); }
    public function source()  { return $this->morphTo(); }

    public function getTypeCouleurAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ENTREE => 'success',
            self::TYPE_SORTIE => 'warning',
            self::TYPE_AJUSTEMENT => 'info',
            self::TYPE_TRANSFERT => 'primary',
            default => 'secondary',
        };
    }
}
