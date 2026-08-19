<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Evaluation extends Model {
    protected $table = 'intranet_evaluations';
    protected $fillable = ['titre','user_id','evaluateur_id','objectif_id','score','commentaire','points_forts','axes_amelioration','date_evaluation','statut'];
    protected $casts = ['date_evaluation'=>'date'];
    public function utilisateur() { return $this->belongsTo(User::class,'user_id'); }
    public function evaluateur() { return $this->belongsTo(User::class,'evaluateur_id'); }
    public function objectif() { return $this->belongsTo(Objectif::class,'objectif_id'); }
}
