<?php
namespace App\Models\Intranet;
use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use Illuminate\Database\Eloquent\Model;
class ProjetProbleme extends Model {
    use HasCommentaires;
    protected $table = 'intranet_projet_problemes';
    protected $fillable = ['projet_id','titre','description','categorie','priorite_id','statut','impact','resolution','responsable_id','risque_id','date_identification','date_resolution','created_by'];
    protected $casts = ['date_identification'=>'date','date_resolution'=>'date'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function priorite() { return $this->belongsTo(Priorite::class,'priorite_id'); }
    public function responsable() { return $this->belongsTo(User::class,'responsable_id'); }
    public function risque() { return $this->belongsTo(ProjetRisque::class,'risque_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
