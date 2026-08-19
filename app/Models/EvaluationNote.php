<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationNote extends Model
{
    protected $table = 'evaluation_notes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['note' => 'decimal:2'];
    }

    public function evaluation() { return $this->belongsTo(EvaluationPrestataire::class, 'evaluation_id'); }
    public function critere() { return $this->belongsTo(CritereEvaluation::class, 'critere_id'); }
}
