<?php

namespace App\Services\Mail;

use App\Models\Finance\Ordre;
use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\MailSuivi;
use App\Models\Intranet\Opportunite;
use App\Models\OperationFinanciere;
use App\Models\User;
use App\Services\Social\ShareableResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Entités auxquelles un mail peut être lié. Réutilise le registre du partage social
 * (projets, tâches, courriers, factures, commandes, tickets…, avec sa recherche filtrée
 * par droits) et ajoute le CRM et les dépenses, contrôlés par permission.
 */
class MailLiens
{
    /** Types propres au mail : clé → [classe, permission de lecture, libellé, icône, colonne titre, colonne détail, route]. */
    private const EXTRA = [
        'opportunite'  => [Opportunite::class, 'read:opportunite', 'Opportunité', 'fa-fire-flame-curved', 'titre', 'reference', 'intranet.opportunites.show', ['titre', 'reference']],
        'contact'      => [Contact::class, 'read:contact', 'Contact', 'fa-address-card', 'nom', 'email', 'intranet.contacts.show', ['nom', 'prenoms', 'email']],
        'organisation' => [ContactOrganisation::class, 'read:organisation_crm', 'Organisation', 'fa-building', 'nom', 'email', 'intranet.organisations.show', ['nom', 'email']],
        'operation'    => [OperationFinanciere::class, 'read:operation', 'Opération financière', 'fa-money-bill-transfer', 'objet', 'numero', 'finance.operations.show', ['objet', 'numero']],
        'ordre'        => [Ordre::class, 'read:operation', 'Ordre de dépense / recette', 'fa-file-invoice', 'libelle', 'numero_ordre', 'finance.ordres.show', ['libelle', 'numero_ordre']],
    ];

    /** Groupes affichés dans le sélecteur (types du registre social pertinents pour un mail). */
    public const GROUPES = [
        'Projets'         => ['projet', 'tache'],
        'Intranet'        => ['courrier', 'ressource', 'evenement', 'annonce'],
        'CRM'             => ['opportunite', 'contact', 'organisation'],
        'Finance'         => ['operation', 'ordre', 'facture'],
        'Achats & MG'     => ['commande', 'demande', 'devis', 'livraison', 'dysfonc', 'intervention', 'immo'],
        'RH'              => ['employee', 'absence', 'formation'],
        'Stratégie'       => ['objectif', 'kpi'],
    ];

    /** @return array<string, string> type → libellé, pour les types que l'utilisateur peut utiliser */
    public static function types(User $user): array
    {
        $res = [];
        foreach (self::GROUPES as $types) {
            foreach ($types as $t) {
                if (isset(self::EXTRA[$t])) {
                    if ($user->can(self::EXTRA[$t][1])) $res[$t] = self::EXTRA[$t][2];
                } elseif (isset(ShareableResolver::TYPES[$t])) {
                    $res[$t] = ShareableResolver::META[ShareableResolver::TYPES[$t]]['label'] ?? $t;
                }
            }
        }
        return $res;
    }

    public static function classe(string $type): ?string
    {
        return self::EXTRA[$type][0] ?? ShareableResolver::TYPES[$type] ?? null;
    }

    public static function typeDe(Model $m): ?string
    {
        foreach (self::EXTRA as $t => $def) if ($m instanceof $def[0]) return $t;
        $t = array_search(get_class($m), ShareableResolver::TYPES, true);
        return $t === false ? null : $t;
    }

    /** Recherche des entités liables (filtrée par droits). */
    public static function rechercher(User $user, string $q, ?string $type = null): array
    {
        $q = trim($q);
        $types = $type ? [$type] : array_keys(self::types($user));
        $res = [];
        foreach ($types as $t) {
            if (!array_key_exists($t, self::types($user))) continue;
            if (isset(self::EXTRA[$t])) {
                [$classe, , , , , , , $cols] = self::EXTRA[$t];
                if ($q === '' && !$type) continue;
                $like = '%'.addcslashes($q, '%_\\').'%';
                $items = $classe::query()
                    ->when($q !== '', fn($x) => $x->where(fn($w) => collect($cols)->each(fn($c) => $w->orWhere($c, 'ilike', $like))))
                    ->latest('id')->limit($type ? 10 : 4)->get();
                foreach ($items as $m) $res[] = self::normaliser($m);
            } else {
                // Point de sauvegarde annulé après lecture : une requête défaillante d'un type
                // (le registre social journalise et ignore ses erreurs) ne bloque pas la transaction en cours.
                DB::beginTransaction();
                try {
                    foreach (ShareableResolver::search($user, $q, $type ? 10 : 4, 'all', $t, (bool) $type) as $r) {
                        $res[] = $r + ['cle' => $r['type'].':'.$r['id']];
                    }
                } finally {
                    DB::rollBack();
                }
            }
        }
        return $res;
    }

    /** Entité à partir d'une clé « type:id », si l'utilisateur peut y accéder. */
    public static function resoudre(string $cle, User $user): ?Model
    {
        if (!preg_match('/^([a-z]+):(\d+)$/', $cle, $m)) return null;
        $classe = self::classe($m[1]);
        if (!$classe || !array_key_exists($m[1], self::types($user))) return null;
        $modele = $classe::find((int) $m[2]);
        if (!$modele) return null;
        if (isset(self::EXTRA[$m[1]])) return $modele; // permission déjà vérifiée via types()
        return ShareableResolver::userCanShare($user, $modele) ? $modele : null;
    }

    /** Représentation affichable (libellé, icône, titre, détail, url, clé). */
    public static function normaliser(Model $m): array
    {
        $t = self::typeDe($m);
        if ($t && isset(self::EXTRA[$t])) {
            [, , $label, $icone, $titre, $detail, $route] = self::EXTRA[$t];
            $titreVal = $m instanceof Contact ? trim($m->prenoms.' '.$m->nom) : (string) $m->{$titre};
            $url = null;
            try { $url = route($route, $m); } catch (\Throwable) {}
            return ['type' => $t, 'id' => (int) $m->id, 'cle' => $t.':'.$m->id, 'label' => $label, 'icon' => $icone,
                'title' => mb_substr($titreVal ?: '#'.$m->id, 0, 120), 'excerpt' => $m->{$detail} ? (string) $m->{$detail} : null, 'url' => $url];
        }
        $n = ShareableResolver::normalize($m);
        return $n + ['cle' => $n['type'].':'.$n['id']];
    }

    /** Mails liés à une entité, visibles par l'utilisateur (les mails confidentiels ne sont vus que par leur propriétaire). */
    public static function mailsLies(Model $entite, User $user)
    {
        return MailSuivi::with('utilisateur:id,name,prenoms')
            ->whereHas('liens', fn($q) => $q->where('liable_type', $entite->getMorphClass())->where('liable_id', $entite->getKey()))
            ->where(fn($q) => $q->where('confidentiel', false)->orWhere('user_id', $user->id))
            ->orderByDesc('date_mail')->limit(50)->get();
    }
}
