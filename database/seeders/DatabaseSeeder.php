<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            PaysSeeder::class,
            SecteursActiviteSeeder::class,
            CrmEtapesSeeder::class,
            IntranetModuleParametresSeeder::class,
            TemplateCategoriesSeeder::class,
            TemplateSeeder::class,
        ]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@optimize.local'],
            [
                'name' => 'Administrateur',
                'prenoms' => 'Systeme',
                'password' => bcrypt('password'),
                'contact' => '000000000',
                'statut' => 1,
            ]
        );
        $admin->assignRole('super-admin');

        // Create test user
        $testUser = User::firstOrCreate(
            ['email' => 'test@optimize.local'],
            [
                'name' => 'Utilisateur',
                'prenoms' => 'Test',
                'password' => bcrypt('password'),
                'contact' => '000000001',
                'statut' => 1,
            ]
        );
        $testUser->assignRole('user');

        $this->call([
            IntranetSeeder::class,
            TypesDocumentsSeeder::class,
            AnnuaireSeeder::class,
            ObjectifsSeeder::class,
            ErpDemoSeeder::class,
            // ─── Finance ───────────────────────────────────────
            // Référentiel (titres, lignes, rubriques) — utilisable en prod
            FinanceReferentielsSeeder::class,
            OrdreModelesSeeder::class,
            // Comptes OHADA pour les écritures de paie
            ComptesPaieOhadaSeeder::class,
            // Données de démo Finance (clients, budgets, factures, opérations avec détails)
            FinanceDemoSeeder::class,
            // ───────────────────────────────────────────────────
            VitrineSeeder::class,
        ]);
    }
}
