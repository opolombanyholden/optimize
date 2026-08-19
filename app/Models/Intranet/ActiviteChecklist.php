<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class ActiviteChecklist extends Model {
    protected $table = 'intranet_activite_checklist';
    protected $fillable = ['activite_id','titre','complete','ordre','complete_par','complete_le','created_by'];
    protected $casts = ['complete'=>'boolean','complete_le'=>'datetime'];
    public function activite() { return $this->belongsTo(Activite::class,'activite_id'); }
    public function completePar() { return $this->belongsTo(User::class,'complete_par'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
