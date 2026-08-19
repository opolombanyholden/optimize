<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'finance' => [
                'exercice', 'budget', 'grandlivre', 'compte',
                'titre', 'ligne', 'modification_budgetaire', 'transaction',
                'client', 'facture', 'operation', 'rubrique_operation',
            ],
            'rh' => [
                'employee', 'absence', 'paie', 'competence',
                'qualification', 'formation', 'recrutement',
                'postulant', 'mission', 'affilie', 'embauche',
                'rubrique', 'evenement_carriere', 'sanction',
                'depart', 'performance', 'conge_solde', 'planning',
                'payement', 'payement_global',
            ],
            'achat' => [
                'fournisseur', 'produit', 'commande',
                'approvisionnement',
            ],
            'mg' => [
                'immobilisation', 'dysfonctionnement', 'intervention',
            ],
            'systeme' => [
                'user', 'role', 'permission', 'organisation',
                'entite', 'config',
            ],
            'intranet' => [
                'annonce', 'news', 'evenement', 'evenement_participation', 'courrier',
                'ressource', 'media', 'archive', 'template',
                'projet_intranet', 'tache_intranet', 'rapport',
                'objectif', 'kpi', 'evaluation', 'plan_action',
                'wiki', 'contact', 'organisation_crm',
                'opportunite', 'groupe',
            ],
        ];

        $actions = ['create', 'read', 'update', 'delete', 'validate', 'export', 'publish', 'assign', 'process'];

        foreach ($modules as $module => $entities) {
            foreach ($entities as $entity) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate([
                        'name' => "{$action}:{$entity}",
                        'guard_name' => 'web',
                    ]);
                }
            }
        }

        // Super Admin - toutes les permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - toutes sauf systeme:delete et systeme:role/permission
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminPerms = Permission::where('name', 'not like', 'delete:role')
            ->where('name', 'not like', 'delete:permission')
            ->get();
        $admin->syncPermissions($adminPerms);

        // Manager - lecture, validation, export
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerPerms = Permission::whereIn('name', function ($q) {
            $q->select('name')->from('permissions')
                ->where('name', 'like', 'read:%')
                ->orWhere('name', 'like', 'validate:%')
                ->orWhere('name', 'like', 'export:%');
        })->get();
        $manager->syncPermissions($managerPerms);

        // User - lecture seule
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userPerms = Permission::where('name', 'like', 'read:%')->get();
        $user->syncPermissions($userPerms);
    }
}
