<?php
namespace App\Models\Intranet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class ProjetPartiePrenante extends Model {
    protected $table = 'intranet_projet_parties_prenantes';
    protected $fillable = ['projet_id','user_id','contact_id','nom_externe','organisation_externe','role_projet','categorie','interet','influence','engagement_actuel','engagement_desire','strategie_engagement','attentes','preoccupations','created_by'];
    public function projet() { return $this->belongsTo(Projet::class,'projet_id'); }
    public function utilisateur() { return $this->belongsTo(User::class,'user_id'); }
    public function contact() { return $this->belongsTo(Contact::class,'contact_id'); }
    public function auteur() { return $this->belongsTo(User::class,'created_by'); }
    public function getNomAttribute(): string {
        if ($this->user_id) return $this->utilisateur?->name ?? '';
        if ($this->contact_id) return $this->contact?->nom_complet ?? '';
        return $this->nom_externe ?? '';
    }
    public function getScorePouvoirAttribute(): int { return $this->interet * $this->influence; }
}
