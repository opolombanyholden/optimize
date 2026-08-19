<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeOrganisation extends Model
{
    use SoftDeletes;

    protected $table = 'typesorganisations';

    protected $fillable = [
        'label', 'introduction', 'description', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'extra_attributes' => 'array',
        ];
    }

    public function organisations()
    {
        return $this->hasMany(Organisation::class, 'typesorganisation_id');
    }
}
