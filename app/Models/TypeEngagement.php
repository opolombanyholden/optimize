<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeEngagement extends Model
{
    use SoftDeletes;

    protected $table = 'types_engagement';

    protected $fillable = ['code', 'libelle', 'description', 'ordre', 'actif'];

    protected function casts(): array
    {
        return ['actif' => 'boolean', 'ordre' => 'integer'];
    }

    public function scopeActifs($q) { return $q->where('actif', true); }
}
