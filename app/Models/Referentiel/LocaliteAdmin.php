<?php

namespace App\Models\Referentiel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocaliteAdmin extends Model
{
    use SoftDeletes;

    protected $table = 'referentiel_localisations';

    protected $fillable = [
        'type', 'code', 'libelle', 'description', 'parent_id',
        'ordre', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function scopeActif($query) { return $query->where('statut', 1); }
    public function scopeOfType($query, string $type) { return $query->where('type', $type); }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
}
