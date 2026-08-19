<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class ProjetLecon extends Model {
    protected $table = 'intranet_projet_lecons';
    protected $fillable = ['projet_id','titre','type','categorie','description','impact','recommandation','statut','created_by'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
