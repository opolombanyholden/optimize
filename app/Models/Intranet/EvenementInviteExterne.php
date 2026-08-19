<?php

namespace App\Models\Intranet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EvenementInviteExterne extends Model
{
    protected $table = 'intranet_evenement_invites_externes';

    protected $fillable = [
        'evenement_id', 'contact_id', 'nom', 'email',
        'statut', 'token', 'repondu_le',
    ];

    protected $casts = [
        'repondu_le' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EvenementInviteExterne $invite) {
            if (empty($invite->token)) {
                $invite->token = Str::random(48);
            }
        });
    }

    public function evenement()
    {
        return $this->belongsTo(Evenement::class, 'evenement_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function getNomAffichageAttribute(): string
    {
        if ($this->contact) return trim(($this->contact->prenoms ?? '') . ' ' . $this->contact->nom);
        return $this->nom ?: $this->email;
    }
}
