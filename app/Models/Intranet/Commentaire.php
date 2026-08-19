<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Commentaire extends Model {
    use SoftDeletes;
    protected $table = 'intranet_commentaires';
    protected $fillable = ['commentable_type','commentable_id','user_id','contenu','parent_id','est_epingle','modifie_le'];
    protected $casts = ['est_epingle'=>'boolean','modifie_le'=>'datetime'];
    public function commentable() { return $this->morphTo(); }
    public function auteur() { return $this->belongsTo(User::class,'user_id'); }
    public function parent() { return $this->belongsTo(Commentaire::class,'parent_id'); }
    public function reponses() { return $this->hasMany(Commentaire::class,'parent_id')->whereNull('deleted_at'); }
}
