<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Administration des rôles Spatie + matrice de permissions.
 */
class RoleController extends Controller
{
    /**
     * Vérifie que l'appelant est super-admin. À appeler en tête de chaque méthode
     * mutation — un admin non-super qui pourrait modifier rôles/permissions ferait
     * une escalade triviale (s'attribuer plus de droits). La lecture (index/show)
     * reste ouverte aux admins.
     */
    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403,
            'La gestion des rôles et permissions est réservée au super-administrateur.');
    }

    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function show(Role $role)
    {
        // Toutes les permissions groupées par entité (préfixe après `:`)
        $toutes = Permission::orderBy('name')->get();
        $groupes = $toutes->groupBy(function ($p) {
            $parts = explode(':', $p->name);
            return $parts[1] ?? 'divers';
        })->sortKeys();

        $actives = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.show', compact('role', 'groupes', 'actives'));
    }

    public function store(Request $request)
    {
        $this->assertSuperAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
        ]);
        Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        return redirect()->route('admin.roles.index')->with('success', 'Rôle créé. Cliquez sur son nom pour lui affecter des permissions.');
    }

    public function update(Request $request, Role $role)
    {
        $this->assertSuperAdmin();
        // Protection super-admin — nom immuable
        if ($role->name === 'super-admin' && $request->name !== 'super-admin') {
            abort(403, 'Le rôle super-admin ne peut pas être renommé.');
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles', 'name')->ignore($role->id)],
        ]);
        $role->update(['name' => $data['name']]);
        return back()->with('success', 'Rôle renommé.');
    }

    /**
     * Sync massif des permissions attribuées à un rôle.
     */
    public function syncPermissions(Request $request, Role $role)
    {
        $this->assertSuperAdmin();
        // Le super-admin a toujours toutes les permissions
        if ($role->name === 'super-admin') {
            $role->syncPermissions(Permission::all());
            return back()->with('success', 'Super-admin conserve toutes les permissions par défaut.');
        }

        $data = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);
        $role->syncPermissions($data['permissions'] ?? []);
        return back()->with('success', count($data['permissions'] ?? []).' permission(s) affectée(s).');
    }

    public function destroy(Role $role)
    {
        $this->assertSuperAdmin();
        if (in_array($role->name, ['super-admin', 'admin', 'user'], true)) {
            abort(403, "Rôle système '{$role->name}' non supprimable.");
        }
        if ($role->users()->exists()) {
            return back()->with('error', 'Ce rôle est attribué à des utilisateurs — impossible de le supprimer.');
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Rôle supprimé.');
    }
}
