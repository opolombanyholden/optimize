<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeCritere extends Model
{
    protected $table = 'grade_criteres';

    protected $fillable = ['grade_id', 'libelle', 'description', 'obligatoire', 'ordre'];

    protected function casts(): array
    {
        return [
            'obligatoire' => 'boolean',
            'ordre'       => 'integer',
        ];
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
