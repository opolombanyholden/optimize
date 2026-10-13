<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Classement d'un mail (priorité, catégorie, échéance, statut, note) et ses liens vers les modules. */
class MailSuivi extends Model
{
    protected $table = 'mail_suivis';

    protected $fillable = ['compte_id', 'user_id', 'message_id', 'sujet', 'correspondant', 'date_mail', 'sens', 'uid', 'dossier',
        'priorite', 'categorie', 'statut', 'echeance', 'note', 'confidentiel'];

    protected $casts = ['date_mail' => 'datetime', 'echeance' => 'date', 'confidentiel' => 'boolean'];

    public const PRIORITES = [
        'basse'    => ['libelle' => 'Basse',    'couleur' => '#64748B', 'fond' => '#F1F5F9', 'icone' => 'fa-arrow-down'],
        'normale'  => ['libelle' => 'Normale',  'couleur' => '#1E40AF', 'fond' => '#DBEAFE', 'icone' => 'fa-minus'],
        'haute'    => ['libelle' => 'Haute',    'couleur' => '#B45309', 'fond' => '#FEF3C7', 'icone' => 'fa-arrow-up'],
        'urgente'  => ['libelle' => 'Urgente',  'couleur' => '#B91C1C', 'fond' => '#FEE2E2', 'icone' => 'fa-bolt'],
    ];

    public const STATUTS = ['a_traiter' => 'À traiter', 'en_attente' => 'En attente de réponse', 'traite' => 'Traité'];

    public const CATEGORIES = ['Demande', 'Information', 'Devis / offre', 'Commande', 'Facturation', 'Réclamation', 'Contrat / juridique', 'Recrutement', 'Rendez-vous', 'Administratif'];

    public function compte()      { return $this->belongsTo(MailCompte::class, 'compte_id'); }
    public function utilisateur() { return $this->belongsTo(User::class, 'user_id'); }
    public function liens()       { return $this->hasMany(MailSuiviLien::class, 'suivi_id'); }

    /** Échéance dépassée et mail pas encore traité. */
    public function enRetard(): bool
    {
        return $this->echeance && $this->echeance->isPast() && !$this->echeance->isToday() && $this->statut !== 'traite';
    }
}
