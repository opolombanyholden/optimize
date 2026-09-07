<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DysfonctionnementAssignation extends Model
{
    protected $table = 'dysfonctionnement_assignations';

    protected $fillable = [
        'dysfonctionnement_id', 'assignable_type', 'assignable_id',
        'assigne_par', 'commentaire', 'assigne_at', 'retire_at',
    ];

    protected function casts(): array
    {
        return [
            'assigne_at' => 'datetime',
            'retire_at' => 'datetime',
        ];
    }

    public function dysfonctionnement()
    {
        return $this->belongsTo(Dysfonctionnement::class, 'dysfonctionnement_id');
    }

    public function assignable()
    {
        return $this->morphTo();
    }

    public function assigneur()
    {
        return $this->belongsTo(User::class, 'assigne_par');
    }

    public function estActive(): bool
    {
        return $this->retire_at === null;
    }

    public function retirer(?int $userId = null): void
    {
        $this->update(['retire_at' => now()]);
    }
}
