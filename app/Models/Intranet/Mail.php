<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Mail extends Model {
    use SoftDeletes;
    protected $table = 'intranet_mails';
    protected $fillable = ['compte_id','uid','sujet','expediteur','destinataires','cc','bcc','corps_html','corps_texte','dossier','lu','important','date_envoi','created_by'];
    protected $casts = ['destinataires'=>'array','cc'=>'array','bcc'=>'array','lu'=>'boolean','important'=>'boolean','date_envoi'=>'datetime'];
    public function compte() { return $this->belongsTo(MailCompte::class,'compte_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
}
