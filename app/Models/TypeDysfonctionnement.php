<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeDysfonctionnement extends Model
{
    use SoftDeletes;

    protected $table = 'typesdysfonctionnements';

    protected $fillable = [
        'famille_id', 'libelle', 'description', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function famille()
    {
        return $this->belongsTo(FamilleDysfonctionnement::class, 'famille_id');
    }

    public function dysfonctionnements()
    {
        return $this->hasMany(Dysfonctionnement::class, 'type_id');
    }
}
