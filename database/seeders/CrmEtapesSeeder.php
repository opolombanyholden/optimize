<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrmEtapesSeeder extends Seeder
{
    public function run(): void
    {
        $etapes = [
            ['nom'=>'Prospection',  'ordre'=>1, 'couleur'=>'#94A3B8', 'probabilite_defaut'=>10, 'est_gagnee'=>false, 'est_perdue'=>false, 'est_finale'=>false],
            ['nom'=>'Qualification','ordre'=>2, 'couleur'=>'#06B6D4', 'probabilite_defaut'=>25, 'est_gagnee'=>false, 'est_perdue'=>false, 'est_finale'=>false],
            ['nom'=>'Proposition',  'ordre'=>3, 'couleur'=>'#7C3AED', 'probabilite_defaut'=>50, 'est_gagnee'=>false, 'est_perdue'=>false, 'est_finale'=>false],
            ['nom'=>'Négociation',  'ordre'=>4, 'couleur'=>'#F59E0B', 'probabilite_defaut'=>75, 'est_gagnee'=>false, 'est_perdue'=>false, 'est_finale'=>false],
            ['nom'=>'Gagnée',       'ordre'=>5, 'couleur'=>'#16A34A', 'probabilite_defaut'=>100,'est_gagnee'=>true,  'est_perdue'=>false, 'est_finale'=>true],
            ['nom'=>'Perdue',       'ordre'=>6, 'couleur'=>'#DC2626', 'probabilite_defaut'=>0,  'est_gagnee'=>false, 'est_perdue'=>true,  'est_finale'=>true],
        ];

        $now = now();
        foreach ($etapes as $row) {
            DB::table('intranet_crm_etapes')->updateOrInsert(
                ['nom' => $row['nom']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
