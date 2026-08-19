<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class MailCompte extends Model {
    protected $table = 'intranet_mail_comptes';
    protected $fillable = ['nom','email','protocole','serveur_entrant','port_entrant','ssl_entrant','serveur_sortant','port_sortant','ssl_sortant','identifiant','mot_de_passe','is_default','is_shared','user_id'];
    protected $hidden = ['mot_de_passe'];
    protected $casts = ['ssl_entrant'=>'boolean','ssl_sortant'=>'boolean','is_default'=>'boolean','is_shared'=>'boolean'];
    public function utilisateur() { return $this->belongsTo(User::class,'user_id'); }
    public function mails() { return $this->hasMany(Mail::class,'compte_id'); }
}
