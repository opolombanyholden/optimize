<?php

namespace App\Models\Intranet;

use App\Models\User;
use App\Services\Mail\Signature;
use App\Support\HtmlSur;
use Illuminate\Database\Eloquent\Model;

/**
 * Signature de mail d'un utilisateur. Personnelle (contenu compilé + sources HTML/CSS)
 * ou issue d'un modèle d'entreprise (modele_id) : dans ce cas le HTML est calculé à la volée,
 * si bien qu'une modification du modèle s'applique immédiatement à tous.
 */
class MailSignature extends Model
{
    protected $table = 'mail_signatures';

    protected $fillable = ['user_id', 'nom', 'contenu', 'source_html', 'source_css', 'par_defaut', 'modele_id'];

    protected $casts = ['par_defaut' => 'boolean'];

    public function utilisateur() { return $this->belongsTo(User::class, 'user_id'); }
    public function modele()      { return $this->belongsTo(MailSignatureModele::class, 'modele_id'); }

    public function setContenuAttribute(?string $html): void
    {
        $this->attributes['contenu'] = HtmlSur::nettoyer((string) $html);
    }

    /** HTML final de la signature pour son propriétaire (variables remplacées). */
    public function html(): string
    {
        $user = $this->utilisateur ?? auth()->user();
        $base = $this->modele_id ? (string) $this->modele?->contenu : (string) $this->contenu;
        return $user ? Signature::rendre($base, $user) : $base;
    }

    public function estVerrouillee(): bool
    {
        return (bool) $this->modele?->verrouille;
    }
}
