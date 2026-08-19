<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Affilie extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'liens', 'noms', 'prenoms',
        'date_naissance', 'contact1', 'contact2', 'email',
        'employee_id', 'fichiersjoin', 'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'extra_attributes' => 'array',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
