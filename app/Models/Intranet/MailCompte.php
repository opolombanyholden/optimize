<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Compte mail professionnel connecté au profil d'un utilisateur (IMAP + SMTP).
 * ssl_entrant / ssl_sortant : true = SSL/TLS implicite (993 / 465), false = STARTTLS (143 / 587).
 */
class MailCompte extends Model {
    protected $table = 'intranet_mail_comptes';
    protected $fillable = ['nom','email','nom_expediteur','protocole','serveur_entrant','port_entrant','ssl_entrant','serveur_sortant','port_sortant','ssl_sortant','identifiant','mot_de_passe','is_default','is_shared','user_id','derniere_connexion_at','derniere_erreur'];
    protected $hidden = ['mot_de_passe'];
    protected $casts = ['ssl_entrant'=>'boolean','ssl_sortant'=>'boolean','is_default'=>'boolean','is_shared'=>'boolean','mot_de_passe'=>'encrypted','derniere_connexion_at'=>'datetime'];
    public function utilisateur() { return $this->belongsTo(User::class,'user_id'); }
    public function mails() { return $this->hasMany(Mail::class,'compte_id'); }

    /** Nom affiché dans le champ « De » des mails envoyés. */
    public function getExpediteurAttribute(): string
    {
        return $this->nom_expediteur ?: trim(($this->utilisateur?->prenoms ?? '').' '.($this->utilisateur?->name ?? ''));
    }
}
