<?php

namespace App\Models\Intranet;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Modèle de signature d'entreprise (variables {{…}}), appliqué à tout ou partie des utilisateurs. */
class MailSignatureModele extends Model
{
    protected $table = 'mail_signature_modeles';

    protected $fillable = ['nom', 'source_html', 'source_css', 'contenu', 'actif', 'verrouille', 'created_by'];

    protected $casts = ['actif' => 'boolean', 'verrouille' => 'boolean'];

    public function signatures() { return $this->hasMany(MailSignature::class, 'modele_id'); }
    public function auteur()     { return $this->belongsTo(User::class, 'created_by'); }
}
