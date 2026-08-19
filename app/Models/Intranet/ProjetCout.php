<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class ProjetCout extends Model {
    protected $table = 'intranet_projet_couts';
    protected $fillable = ['projet_id','phase_id','tache_id','activite_id','libelle','categorie','montant_estime','montant_reel','date_cout','notes','created_by'];
    protected $casts = ['date_cout'=>'date','montant_estime'=>'decimal:2','montant_reel'=>'decimal:2'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function phase() { return $this->belongsTo(ProjetPhase::class,'phase_id'); }
    public function tache() { return $this->belongsTo(Tache::class,'tache_id'); }
    public function activite() { return $this->belongsTo(Activite::class,'activite_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function getEcartAttribute(): float { return $this->montant_reel - $this->montant_estime; }
}
