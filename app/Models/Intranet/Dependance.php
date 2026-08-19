<?php
namespace App\Models\Intranet;
use Illuminate\Database\Eloquent\Model;
class Dependance extends Model {
    protected $table = 'intranet_dependances';
    protected $fillable = ['source_type','source_id','cible_type','cible_id','type','lag_jours'];
    public function source() { return $this->morphTo('source'); }
    public function cible() { return $this->morphTo('cible'); }
    public function getLibelleTypeAttribute(): string {
        return match($this->type) {
            'FS' => 'Fin → Début',
            'SS' => 'Début → Début',
            'FF' => 'Fin → Fin',
            'SF' => 'Début → Fin',
        };
    }
}
