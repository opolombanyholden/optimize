<?php
namespace App\Models\Intranet;
use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use Illuminate\Database\Eloquent\Model;
class ProjetLivrable extends Model {
    use HasCommentaires;
    protected $table = 'intranet_projet_livrables';
    protected $fillable = ['projet_id','phase_id','tache_id','titre','description','criteres_acceptation','statut','date_prevue','date_livraison','responsable_id','created_by'];
    protected $casts = ['date_prevue'=>'date','date_livraison'=>'date'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function phase() { return $this->belongsTo(ProjetPhase::class,'phase_id'); }
    public function tache() { return $this->belongsTo(Tache::class,'tache_id'); }
    public function responsable() { return $this->belongsTo(User::class,'responsable_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function estEnRetard(): bool {
        return in_array($this->statut,['planifie','en_cours']) && $this->date_prevue?->isPast();
    }
}
