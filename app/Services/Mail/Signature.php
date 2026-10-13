<?php

namespace App\Services\Mail;

use App\Models\SystemeConfig;
use App\Models\User;
use App\Support\HtmlSur;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Signatures de mail : compilation HTML/CSS (CSS passé en ligne, seule forme fiable dans
 * les messageries), nettoyage par liste blanche et variables {{…}} des modèles d'entreprise.
 */
class Signature
{
    /** Variables disponibles dans les modèles : clé → libellé. */
    public const VARIABLES = [
        'prenom'      => 'Prénom',
        'nom'         => 'Nom',
        'nom_complet' => 'Prénom et nom',
        'poste'       => 'Poste',
        'email'       => 'Email',
        'telephone'   => 'Téléphone',
        'matricule'   => 'Matricule',
        'bureau'      => 'Bureau',
        'linkedin'    => 'LinkedIn',
        'entreprise'  => 'Entreprise',
    ];

    /** HTML + CSS saisis → HTML avec styles en ligne, nettoyé. */
    public static function compiler(string $html, ?string $css = null): string
    {
        $css = trim((string) $css);
        if ($css !== '') {
            // Neutralise ce que l'inliner pourrait recopier tel quel (url(), @import…) ; HtmlSur filtre ensuite chaque propriété
            $css = preg_replace('/@import[^;]*;?|url\s*\([^)]*\)|expression\s*\([^)]*\)/i', '', $css);
            $doc = (new CssToInlineStyles())->convert('<html><body><div id="sig-racine">'.$html.'</div></body></html>', $css);
            if (preg_match('#<div id="sig-racine"[^>]*>(.*)</div>\s*</body>#is', $doc, $m)) $html = $m[1];
        }
        return HtmlSur::nettoyer($html);
    }

    /** Valeurs des variables pour un utilisateur. */
    public static function valeurs(User $user): array
    {
        $prenom = trim((string) $user->prenoms);
        $nom = trim((string) $user->name);
        $email = $user->comptesMail()->where('is_default', true)->value('email') ?: $user->email;
        return [
            'prenom'      => $prenom,
            'nom'         => $nom,
            'nom_complet' => trim("{$prenom} {$nom}"),
            'poste'       => (string) $user->poste,
            'email'       => (string) $email,
            'telephone'   => (string) $user->contact,
            'matricule'   => (string) $user->matricule,
            'bureau'      => (string) $user->bureau,
            'linkedin'    => (string) $user->linkedin,
            'entreprise'  => (string) (self::entreprise()),
        ];
    }

    /** Remplace les variables {{x}} (aussi sous forme encodée dans les liens) par les valeurs échappées. */
    public static function rendre(string $contenu, User $user): string
    {
        $rempl = [];
        foreach (self::valeurs($user) as $cle => $val) {
            $v = e($val);
            foreach (['{{'.$cle.'}}', '{{ '.$cle.' }}', '%7B%7B'.$cle.'%7D%7D', '%7B%7B%20'.$cle.'%20%7D%7D'] as $motif) $rempl[$motif] = $v;
        }
        return strtr($contenu, $rempl);
    }

    private static function entreprise(): string
    {
        try {
            return (string) (SystemeConfig::get('app_nom') ?: config('app.name'));
        } catch (\Throwable) {
            return (string) config('app.name');
        }
    }
}
