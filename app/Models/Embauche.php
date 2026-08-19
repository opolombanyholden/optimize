<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Embauche extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'introduction', 'description', 'valider',
        'postulant_id', 'employee_id', 'fichiersjoin',
        'statut', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return ['extra_attributes' => 'array'];
    }

    public function postulant()
    {
        return $this->belongsTo(Postulant::class, 'postulant_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
