<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Like extends Model {
    public $timestamps = false;
    protected $table = 'intranet_likes';
    protected $fillable = ['likeable_type','likeable_id','user_id'];
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    public function likeable() { return $this->morphTo(); }
    public function auteur() { return $this->belongsTo(User::class,'user_id'); }
}
