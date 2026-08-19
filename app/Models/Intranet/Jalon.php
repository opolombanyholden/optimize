<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Traits\Intranet\HasPiecesJointes;
use App\Traits\Intranet\HasProtection;
use App\Traits\Intranet\HasValidation;
use Illuminate\Database\Eloquent\Model;

class Jalon extends Model
{
    use HasValidation, HasPiecesJointes, HasProtection;

    protected $table = 'intranet_jalons';

    protected $fillable = [
        'projet_id', 'phase_id', 'titre', 'description',
        'date_prevue', 'date_reelle', 'statut', 'created_by',
        'statut_cloture', 'justification_cloture', 'soumis_le', 'valide_le', 'motif_rejet',
    ];

    protected $casts = [
        'date_prevue' => 'date',
        'date_reelle' => 'date',
        'soumis_le'   => 'datetime',
        'valide_le'   => 'datetime',
    ];

    public function projet()  { return $this->belongsTo(Projet::class, 'projet_id'); }
    public function phase()   { return $this->belongsTo(ProjetPhase::class, 'phase_id'); }
    public function auteur()  { return $this->belongsTo(User::class, 'created_by'); }

    protected function determinerVerrouillage(): bool
    {
        // Un jalon est verrouillé s'il n'est plus "prévu" ou a un historique de validation
        return $this->statut !== 'prevu'
            || $this->historiqueValidation()->exists();
    }

    public function estEnRetard(): bool
    {
        return $this->statut === 'prevu' && $this->date_prevue?->isPast();
    }
}
