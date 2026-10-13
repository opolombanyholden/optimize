<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManipuleTableurs;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Administration des utilisateurs — CRUD + rôles + reset password + activation
 * + import / export (CSV `;` ou XLSX).
 * Réservé aux rôles super-admin / admin.
 */
class UserController extends Controller
{
    use ManipuleTableurs;

    public function index(Request $request)
    {
        $users = $this->buildQuery($request)
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get(['id', 'name']);
        $stats = [
            'total'   => User::count(),
            'actifs'  => User::where('statut', 1)->count(),
            'inactifs'=> User::where('statut', 0)->orWhereNull('statut')->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'stats'));
    }

    /** Requête filtrée commune à la liste et à l'export (q / role / statut). */
    private function buildQuery(Request $request)
    {
        return User::with('roles:id,name')
            ->when($request->q, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'ilike', "%{$s}%")
                  ->orWhere('prenoms', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
                  ->orWhere('matricule', 'ilike', "%{$s}%");
            }))
            ->when($request->role, fn($q, $r) => $q->whereHas('roles', fn($x) => $x->where('name', $r)))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderBy('name');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->assertPeutAttribuerRoles($data['roles'] ?? []);

        $motDePasseGenere = null;
        if (empty($data['password'])) {
            $motDePasseGenere = Str::password(12, symbols: false);
            $data['password'] = $motDePasseGenere;
        }

        $user = User::create([
            'name'                  => $data['name'],
            'prenoms'               => $data['prenoms'] ?? null,
            'email'                 => $data['email'],
            'matricule'             => $data['matricule'] ?? null,
            'contact'               => $data['contact'] ?? null,
            'poste'                 => $data['poste'] ?? null,
            'statut'                => 1,
            'password'              => Hash::make($data['password']),
            'must_change_password'  => (bool) ($data['must_change_password'] ?? true),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        $msg = "Utilisateur créé.";
        if ($motDePasseGenere) $msg .= " Mot de passe temporaire : {$motDePasseGenere} (à communiquer au collaborateur).";

        return redirect()->route('admin.users.index')->with('success', $msg);
    }

    public function update(Request $request, User $user)
    {
        $this->assertPeutAgirSurUser($user);
        $data = $this->validateData($request, $user);
        $this->assertPeutAttribuerRoles($data['roles'] ?? []);
        unset($data['password']); // password géré séparément

        $user->update([
            'name'      => $data['name'],
            'prenoms'   => $data['prenoms'] ?? null,
            'email'     => $data['email'],
            'matricule' => $data['matricule'] ?? null,
            'contact'   => $data['contact'] ?? null,
            'poste'     => $data['poste'] ?? null,
        ]);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    public function toggleActif(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Vous ne pouvez pas désactiver votre propre compte.');
        $this->assertPeutAgirSurUser($user);
        $user->update(['statut' => $user->statut ? 0 : 1]);
        return back()->with('success', $user->statut ? "Compte activé." : "Compte désactivé.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->assertPeutAgirSurUser($user);
        $data = $request->validate([
            'nouveau_mdp' => 'nullable|string|min:8|max:100',
        ]);

        $mdp = $data['nouveau_mdp'] ?? Str::password(12, symbols: false);
        $user->update([
            'password'              => Hash::make($mdp),
            'must_change_password'  => true,
            'password_reset_by'     => auth()->id(),
            'password_reset_at'     => now(),
        ]);

        return back()->with('success', "Mot de passe réinitialisé : {$mdp} (à communiquer, à changer au 1er login).");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Vous ne pouvez pas supprimer votre propre compte.');
        $this->assertPeutAgirSurUser($user);
        abort_if($user->hasRole('super-admin') && User::role('super-admin')->count() <= 1, 403,
            'Impossible de supprimer le dernier super-admin.');

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé.');
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT — csv | xlsx, respecte les filtres de la liste
    // Les en-têtes sont identiques à ceux de l'import (aller-retour possible).
    // ═══════════════════════════════════════════════════════════════
    private const COLONNES = ['nom', 'prenoms', 'email', 'matricule', 'poste', 'contact', 'roles', 'statut'];

    public function export(Request $request): StreamedResponse
    {
        $q = $this->buildQuery($request);

        return $this->telechargerTableur(
            strtolower((string) $request->query('format', 'csv')),
            'utilisateurs', 'Utilisateurs', self::COLONNES,
            fn(\Closure $ecrire) => $q->chunk(500, fn($users) => $users->each(fn(User $u) => $ecrire([
                $u->name,
                $u->prenoms,
                $u->email,
                $u->matricule,
                $u->poste,
                $u->contact,
                $u->roles->pluck('name')->implode(','),
                $u->statut ? 'actif' : 'inactif',
            ]))),
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // IMPORT — modèle + traitement
    // ═══════════════════════════════════════════════════════════════
    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [...self::COLONNES, 'mot_de_passe'], ';');
            fputcsv($out, self::safeCsvRow(['MBA', 'Jean Pierre', 'jp.mba@exemple.com', 'MAT-0001', 'Comptable', '+241 01 23 45 67', 'user', 'actif', '']), ';');
            fputcsv($out, ['NZE', 'Aline', 'a.nze@exemple.com', 'MAT-0002', 'Chargée RH', '', 'user,admin', 'actif', ''], ';');
            fclose($out);
        }, 'utilisateurs-modele.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier'              => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'], // 5 Mo max
            'mettre_a_jour'        => ['nullable', 'boolean'],
            'ignore_erreurs'       => ['nullable', 'boolean'],
            'must_change_password' => ['nullable', 'boolean'],
        ]);

        $rows = $this->lireTableur($request->file('fichier'));

        if ($rows === null) {
            return back()->with('error', 'Impossible de lire le fichier ou fichier vide.');
        }

        $header = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($rows));
        $missing = array_diff(['nom', 'email'], $header);
        if (!empty($missing)) {
            return back()->with('error', 'Colonnes obligatoires manquantes : '.implode(', ', $missing));
        }

        $majExistants = (bool) $request->input('mettre_a_jour');
        $ignore       = (bool) $request->input('ignore_erreurs');
        $mustChange   = (bool) $request->input('must_change_password', true);
        $estSuperAdmin = auth()->user()->hasRole('super-admin');

        // ── Caches : rôles valides + comptes existants (y compris supprimés, l'unicité DB les inclut) ──
        $rolesValides = Role::pluck('name')->all();
        $existantsParEmail = User::withTrashed()->with('roles:id,name')->get()
            ->keyBy(fn($u) => mb_strtolower($u->email));
        $matricules = User::withTrashed()->whereNotNull('matricule')->pluck('id', 'matricule')->all();

        $operations = [];
        $erreurs    = [];
        $vusEmail   = [];
        $vusMatricule = [];
        $lineNum    = 1; // en-tête = ligne 1

        foreach ($rows as $row) {
            $lineNum++;
            if (($data = self::normaliserLigne($header, $row)) === null) continue;
            $val  = fn(string $k) => ($data[$k] ?? '') !== '' ? $data[$k] : null;

            $errs  = [];
            $email = mb_strtolower((string) $val('email'));
            $existant = $existantsParEmail[$email] ?? null;

            if (!$val('nom')) $errs[] = 'nom vide';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errs[] = 'email invalide';
            elseif (isset($vusEmail[$email])) $errs[] = "email en double dans le fichier (ligne {$vusEmail[$email]})";
            elseif ($existant?->trashed()) $errs[] = 'email appartenant à un compte supprimé';
            elseif ($existant && !$majExistants) $errs[] = 'email déjà utilisé (cochez « Mettre à jour les existants »)';
            if ($email) $vusEmail[$email] = $lineNum;

            if ($existant && !$estSuperAdmin && $existant->hasRole('super-admin')) {
                $errs[] = 'seul un super-admin peut modifier un compte super-admin';
            }

            foreach (['nom' => 100, 'prenoms' => 100, 'matricule' => 50, 'poste' => 150, 'contact' => 30] as $k => $max) {
                if (mb_strlen((string) $val($k)) > $max) $errs[] = "{$k} trop long (max {$max})";
            }

            $matricule = $val('matricule');
            if ($matricule) {
                if (isset($vusMatricule[$matricule])) $errs[] = "matricule en double dans le fichier (ligne {$vusMatricule[$matricule]})";
                elseif (isset($matricules[$matricule]) && $matricules[$matricule] !== $existant?->id) $errs[] = "matricule {$matricule} déjà attribué";
                $vusMatricule[$matricule] = $lineNum;
            }

            // Rôles : séparés par virgule, point-virgule ou |
            $roles = null;
            if (array_key_exists('roles', $data)) {
                $roles = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,;|]/', (string) $val('roles'))))));
                if ($inconnus = array_diff($roles, $rolesValides)) $errs[] = 'rôle(s) inconnu(s) : '.implode(', ', $inconnus);
                if (in_array('super-admin', $roles, true) && !$estSuperAdmin) $errs[] = 'seul un super-admin peut attribuer le rôle super-admin';
            }

            $statut = null;
            if ($val('statut') !== null) {
                $statut = self::parseStatut($val('statut'));
                if ($statut === null) $errs[] = 'statut invalide (actif / inactif)';
            }

            $mdp = $val('mot_de_passe');
            if ($mdp !== null && mb_strlen($mdp) < 8) $errs[] = 'mot de passe trop court (min 8)';

            if (!empty($errs)) {
                $erreurs[] = ['ligne' => $lineNum, 'motifs' => $errs];
                continue;
            }

            $operations[] = [
                'user'   => $existant,
                'attrs'  => array_filter([
                    'name'      => $val('nom'),
                    'prenoms'   => $val('prenoms'),
                    'email'     => $email,
                    'matricule' => $matricule,
                    'poste'     => $val('poste'),
                    'contact'   => $val('contact'),
                    'statut'    => $statut,
                ], fn($v) => $v !== null),
                'roles'  => $roles,
                'mdp'    => $mdp,
            ];
        }

        if (!empty($erreurs) && !$ignore) {
            return back()
                ->with('error', count($erreurs).' ligne(s) en erreur — corrigez le fichier ou cochez « Ignorer les lignes en erreur ».')
                ->with('import_erreurs', array_slice($erreurs, 0, 30));
        }

        if (empty($operations)) {
            return back()->with('error', 'Aucune ligne valide à importer.')->with('import_erreurs', array_slice($erreurs, 0, 30));
        }

        $crees = 0;
        $majs  = 0;
        $identifiants = []; // mots de passe générés, affichés une seule fois

        DB::transaction(function () use ($operations, $mustChange, &$crees, &$majs, &$identifiants) {
            foreach ($operations as $op) {
                if ($op['user']) {
                    $op['user']->update($op['attrs']);
                    $user = $op['user'];
                    $majs++;
                } else {
                    $mdp = $op['mdp'] ?? Str::password(12, symbols: false);
                    $user = User::create($op['attrs'] + [
                        'prenoms'              => '', // colonnes NOT NULL en base
                        'contact'              => '',
                        'statut'               => 1,
                        'password'             => Hash::make($mdp),
                        'must_change_password' => $mustChange,
                    ]);
                    if (!$op['mdp']) $identifiants[] = ['email' => $user->email, 'nom' => trim("{$user->prenoms} {$user->name}"), 'mdp' => $mdp];
                    $crees++;
                }
                if ($op['roles'] !== null) $user->syncRoles($op['roles']);
            }
        });

        $msg = "Import terminé : {$crees} créé(s), {$majs} mis à jour.";
        if ($erreurs) $msg .= ' '.count($erreurs).' ligne(s) ignorée(s).';

        return redirect()->route('admin.users.index')
            ->with('success', $msg)
            ->with('import_erreurs', array_slice($erreurs, 0, 30))
            ->with('import_identifiants', $identifiants);
    }

    // ── HELPERS SÉCURITÉ ──────────────────────────────────

    /**
     * Bloque un admin (non-super-admin) qui tenterait d'attribuer le rôle super-admin
     * — sinon escalade triviale : je m'auto-promeus, ou je promeus un complice.
     */
    private function assertPeutAttribuerRoles(array $roles): void
    {
        if (in_array('super-admin', $roles, true) && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Seul un super-admin peut attribuer le rôle super-admin.');
        }
    }

    /**
     * Bloque un admin qui tenterait d'agir sur un compte super-admin
     * (edit, reset password, désactivation, suppression) — sinon il pourrait
     * le neutraliser puis prendre sa place.
     */
    private function assertPeutAgirSurUser(User $user): void
    {
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Seul un super-admin peut agir sur un compte super-admin.');
        }
    }

    private function validateData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name'                 => 'required|string|max:100',
            'prenoms'              => 'nullable|string|max:100',
            'email'                => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'matricule'            => ['nullable', 'string', 'max:50', Rule::unique('users', 'matricule')->ignore($user?->id)],
            'contact'              => 'nullable|string|max:30',
            'poste'                => 'nullable|string|max:150',
            'password'             => 'nullable|string|min:8|max:100',
            'must_change_password' => 'nullable|boolean',
            'roles'                => 'nullable|array',
            'roles.*'              => 'string|exists:roles,name',
        ]);
    }
}
