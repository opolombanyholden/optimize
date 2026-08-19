<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'label', 'type_abscence', 'introduction', 'description',
        'debut', 'fin', 'employee_id', 'fichiersjoin',
        'statut', 'valide_par', 'date_validation', 'extra_attributes',
    ];

    protected function casts(): array
    {
        return [
            'debut' => 'datetime',
            'fin' => 'datetime',
            'date_validation' => 'datetime',
            'extra_attributes' => 'array',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function getDureeJoursAttribute(): ?int
    {
        if ($this->debut && $this->fin) {
            return $this->debut->diffInDays($this->fin);
        }
        return null;
    }

    public function scopeEnAttente($query) { return $query->where('statut', 1); }
    public function scopeValide($query) { return $query->where('statut', 2); }
}
