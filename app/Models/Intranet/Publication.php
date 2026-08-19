<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Publication extends Model {
    protected $table = 'intranet_publications';
    protected $fillable = ['publishable_type','publishable_id','visibilite','likes_actifs','commentaires_actifs','partage_actif','publie_le','expire_le','created_by'];
    protected $casts = ['likes_actifs'=>'boolean','commentaires_actifs'=>'boolean','partage_actif'=>'boolean','publie_le'=>'datetime','expire_le'=>'datetime'];
    public function publishable() { return $this->morphTo(); }
    public function cibles() { return $this->hasMany(PublicationCible::class,'publication_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function ciblesUsers() { return $this->cibles()->where('cible_type','user'); }
    public function ciblesGroupes() { return $this->cibles()->where('cible_type','groupe'); }
    public function ciblesEntites() { return $this->cibles()->where('cible_type','entite'); }
    public function estVisible(User $user): bool {
        if ($this->visibilite === 'public') return true;
        if ($this->visibilite === 'brouillon') return false;
        // vérifier si user est dans les cibles
        if ($this->cibles()->where('cible_type','user')->where('cible_id',$user->id)->exists()) return true;
        // vérifier ses groupes
        $groupeIds = $user->groupesIntranet->pluck('id');
        if ($this->cibles()->where('cible_type','groupe')->whereIn('cible_id',$groupeIds)->exists()) return true;
        // vérifier son entité
        if ($user->organisation_id && $this->cibles()->where('cible_type','entite')->where('cible_id',$user->organisation_id)->exists()) return true;
        return false;
    }
}
