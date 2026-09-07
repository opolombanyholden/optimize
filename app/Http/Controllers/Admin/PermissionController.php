<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

/**
 * Visualisation des permissions du système (lecture seule).
 * Les permissions sont figées par le RolePermissionSeeder — pas de CRUD ici.
 */
class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::withCount('roles')->orderBy('name')->get();

        // Décomposition action:entite pour affichage matriciel
        $matrice = [];
        foreach ($permissions as $p) {
            [$action, $entite] = array_pad(explode(':', $p->name, 2), 2, null);
            if (!$entite) continue;
            $matrice[$entite][$action] = ['id' => $p->id, 'nb_roles' => $p->roles_count, 'name' => $p->name];
        }
        ksort($matrice);

        // Actions dans l'ordre logique
        $actions = ['read', 'create', 'update', 'delete', 'validate', 'export', 'publish', 'assign', 'process'];

        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'matrice'     => $matrice,
            'actions'     => $actions,
            'totalCount'  => $permissions->count(),
        ]);
    }
}
