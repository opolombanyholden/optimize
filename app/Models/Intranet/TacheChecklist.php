<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class TacheChecklist extends Model {
    protected $table = 'intranet_tache_checklist';
    protected $fillable = ['tache_id','titre','complete','ordre','complete_par','complete_le','created_by'];
    protected $casts = ['complete'=>'boolean','complete_le'=>'datetime'];
    public function tache() { return $this->belongsTo(Tache::class,'tache_id'); }
    public function completePar() { return $this->belongsTo(User::class,'complete_par'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
