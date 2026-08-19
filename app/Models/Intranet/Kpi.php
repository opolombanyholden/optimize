<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Kpi extends Model {
    protected $table = 'intranet_kpi';
    protected $fillable = ['titre','description','objectif_id','valeur_cible','valeur_actuelle','unite','tendance','periodicite','created_by'];
    protected $casts = ['valeur_cible'=>'decimal:4','valeur_actuelle'=>'decimal:4'];
    public function objectif() { return $this->belongsTo(Objectif::class,'objectif_id'); }
    public function valeurs() { return $this->hasMany(KpiValeur::class,'kpi_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function getProgressionAttribute() {
        if (!$this->valeur_cible || $this->valeur_cible == 0) return 0;
        return min(100, round(($this->valeur_actuelle / $this->valeur_cible) * 100));
    }
}
