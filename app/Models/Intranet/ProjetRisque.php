<?php
namespace App\Models\Intranet;
use App\Models\User;
use App\Traits\Intranet\HasCommentaires;
use Illuminate\Database\Eloquent\Model;
class ProjetRisque extends Model {
    use HasCommentaires;
    protected $table = 'intranet_projet_risques';
    protected $fillable = ['projet_id','titre','description','categorie','probabilite','impact','type_risque','strategie','plan_reponse','plan_contingence','cout_contingence','statut','responsable_id','date_identification','date_revue','created_by'];
    protected $casts = ['date_identification'=>'date','date_revue'=>'date'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function responsable() { return $this->belongsTo(User::class,'responsable_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function problemes() { return $this->hasMany(ProjetProbleme::class,'risque_id'); }
    public function getNiveauAttribute(): string {
        $s = $this->probabilite * $this->impact;
        if ($s >= 15) return 'critique';
        if ($s >= 8)  return 'eleve';
        if ($s >= 4)  return 'moyen';
        return 'faible';
    }
}
