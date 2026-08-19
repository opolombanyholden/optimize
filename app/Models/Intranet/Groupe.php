<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Groupe extends Model {
    use SoftDeletes;
    protected $table = 'intranet_groupes';
    protected $fillable = ['nom','description','couleur','icone','created_by'];
    public function membres() { return $this->belongsToMany(User::class,'intranet_groupe_user')->withPivot('role')->withTimestamps(); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
