<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Administration des utilisateurs — CRUD + rôles + reset password + activation.
 * Réservé aux rôles super-admin / admin.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles:id,name')
            ->when($request->q, fn($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'ilike', "%{$s}%")
                  ->orWhere('prenoms', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
                  ->orWhere('matricule', 'ilike', "%{$s}%");
            }))
            ->when($request->role, fn($q, $r) => $q->whereHas('roles', fn($x) => $x->where('name', $r)))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', (int) $request->statut))
            ->orderBy('name')
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
