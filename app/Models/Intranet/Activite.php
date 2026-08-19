<?php
namespace App\Models\Intranet;
use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use App\Traits\Intranet\HasLikes;
use App\Traits\Intranet\HasPublication;
use App\Traits\Intranet\HasVues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Activite extends Model {
    use SoftDeletes, HasPublication, HasLikes, HasCommentaires, HasVues;
    protected $table = 'intranet_activites';
    protected $fillable = ['tache_id','titre','description','statut_id','priorite_id','responsable_id','duree_estimee','duree_reelle','date_debut','date_fin','avancement','ordre','created_by'];
    protected $casts = ['date_debut'=>'date','date_fin'=>'date'];
    public function tache() { return $this->belongsTo(Tache::class,'tache_id'); }
    public function statut() { return $this->belongsTo(Statut::class,'statut_id'); }
    public function priorite() { return $this->belongsTo(Priorite::class,'priorite_id'); }
    public function responsable() { return $this->belongsTo(User::class,'responsable_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function checklist() { return $this->hasMany(ActiviteChecklist::class,'activite_id')->orderBy('ordre'); }
    public function saisiesTemps() { return $this->hasMany(FeuilleTemps::class,'activite_id'); }
    public function cibles() { return $this->morphMany(\App\Models\Intranet\Dependance::class,'cible'); }
    public function sources() { return $this->morphMany(\App\Models\Intranet\Dependance::class,'source'); }
    public function getTotalHeuresAttribute(): float {
        return (float) $this->saisiesTemps()->sum('heures');
    }
}
