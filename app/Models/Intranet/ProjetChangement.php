<?php
namespace App\Models\Intranet;
use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use Illuminate\Database\Eloquent\Model;
class ProjetChangement extends Model {
    use HasCommentaires;
    protected $table = 'intranet_projet_changements';
    protected $fillable = ['projet_id','titre','description','type','justification','impact_cout','impact_delai_jours','impact_qualite','impact_risques','statut','demandeur_id','approuve_par','date_soumission','date_decision','decision_commentaire','created_by'];
    protected $casts = ['date_soumission'=>'date','date_decision'=>'date'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function demandeur() { return $this->belongsTo(User::class,'demandeur_id'); }
    public function approbateur() { return $this->belongsTo(User::class,'approuve_par'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
