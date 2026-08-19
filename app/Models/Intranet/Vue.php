<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Vue extends Model {
    public $timestamps = false;
    protected $table = 'intranet_vues';
    protected $fillable = ['viewable_type','viewable_id','user_id'];
    const CREATED_AT = 'vu_le';
    const UPDATED_AT = null;
    public function viewable() { return $this->morphTo(); }
    public function utilisateur() { return $this->belongsTo(User::class,'user_id'); }
}
