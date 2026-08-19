<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'introduction', 'description',
        'typesorganisation_id', 'chefs', 'peres',
        'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
        ];
    }

    public function type()
    {
        return $this->belongsTo(TypeOrganisation::class, 'typesorganisation_id');
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chefs');
    }

    public function parent()
    {
        return $this->belongsTo(Organisation::class, 'peres');
    }

    public function enfants()
    {
        return $this->hasMany(Organisation::class, 'peres');
    }
}
