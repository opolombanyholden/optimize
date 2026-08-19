<?php

namespace App\Notifications;

use App\Models\CommandeInterne;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommandeInterneAssigneeN1 extends Notification
{
    use Queueable;

    public function __construct(
        public CommandeInterne $commande,
        public User $n1,
        public User $auteur,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'commande_interne.assignee_n1',
            'commande_id'  => $this->commande->id,
            'numero'       => $this->commande->numero,
            'objet'        => $this->commande->objet,
            'n1_id'        => $this->n1->id,
            'n1_name'      => trim(($this->n1->name ?? '') . ' ' . ($this->n1->prenoms ?? '')) ?: $this->n1->email,
            'assignee_par' => $this->auteur->name . ' ' . ($this->auteur->prenoms ?? ''),
            'url'          => route('appro.commandes-internes.show', $this->commande, false),
            'message'      => "Votre demande {$this->commande->numero} a été transmise à {$this->n1->name} pour validation.",
        ];
    }
}
