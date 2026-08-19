<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class FeuilleTemps extends Model {
    protected $table = 'intranet_feuilles_temps';
    protected $fillable = ['user_id','projet_id','tache_id','activite_id','date','heures','description','statut','approuve_par','approuve_le'];
    protected $casts = ['date'=>'date','approuve_le'=>'datetime'];
    public function utilisateur() { return $this->belongsTo(User::class,'user_id'); }
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function tache() { return $this->belongsTo(Tache::class,'tache_id'); }
    public function activite() { return $this->belongsTo(Activite::class,'activite_id'); }
    public function approbateur() { return $this->belongsTo(User::class,'approuve_par'); }
}
