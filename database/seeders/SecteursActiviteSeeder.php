<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecteursActiviteSeeder extends Seeder
{
    public function run(): void
    {
        $secteurs = [
            ['code'=>'agriculture','nom'=>'Agriculture','couleur'=>'#65A30D','icone'=>'fa-seedling'],
            ['code'=>'agroalimentaire','nom'=>'Agroalimentaire','couleur'=>'#84CC16','icone'=>'fa-wheat-awn'],
            ['code'=>'architecture','nom'=>'Architecture','couleur'=>'#0EA5E9','icone'=>'fa-compass-drafting'],
            ['code'=>'assurance','nom'=>'Assurance','couleur'=>'#0891B2','icone'=>'fa-shield-halved'],
            ['code'=>'automobile','nom'=>'Automobile','couleur'=>'#475569','icone'=>'fa-car'],
            ['code'=>'banque','nom'=>'Banque & Finance','couleur'=>'#4F46E5','icone'=>'fa-building-columns'],
            ['code'=>'btp','nom'=>'BTP & Construction','couleur'=>'#D97706','icone'=>'fa-helmet-safety'],
            ['code'=>'chimie','nom'=>'Chimie','couleur'=>'#7C3AED','icone'=>'fa-flask'],
            ['code'=>'commerce','nom'=>'Commerce de détail','couleur'=>'#EC4899','icone'=>'fa-store'],
            ['code'=>'conseil','nom'=>'Conseil','couleur'=>'#0D9488','icone'=>'fa-handshake'],
            ['code'=>'cosmetique','nom'=>'Cosmétique','couleur'=>'#DB2777','icone'=>'fa-spray-can-sparkles'],
            ['code'=>'distribution','nom'=>'Distribution','couleur'=>'#EA580C','icone'=>'fa-boxes-stacked'],
            ['code'=>'edition','nom'=>'Édition & Médias','couleur'=>'#9333EA','icone'=>'fa-newspaper'],
            ['code'=>'education','nom'=>'Éducation','couleur'=>'#2563EB','icone'=>'fa-graduation-cap'],
            ['code'=>'energie','nom'=>'Énergie','couleur'=>'#F59E0B','icone'=>'fa-bolt'],
            ['code'=>'environnement','nom'=>'Environnement','couleur'=>'#16A34A','icone'=>'fa-leaf'],
            ['code'=>'hotellerie','nom'=>'Hôtellerie & Restauration','couleur'=>'#C026D3','icone'=>'fa-utensils'],
            ['code'=>'immobilier','nom'=>'Immobilier','couleur'=>'#0369A1','icone'=>'fa-house'],
            ['code'=>'industrie','nom'=>'Industrie','couleur'=>'#525252','icone'=>'fa-industry'],
            ['code'=>'tech','nom'=>'Informatique & Tech','couleur'=>'#7C3AED','icone'=>'fa-microchip'],
            ['code'=>'logistique','nom'=>'Logistique & Transport','couleur'=>'#0284C7','icone'=>'fa-truck'],
            ['code'=>'luxe','nom'=>'Luxe','couleur'=>'#A16207','icone'=>'fa-gem'],
            ['code'=>'marketing','nom'=>'Marketing & Communication','couleur'=>'#F43F5E','icone'=>'fa-bullhorn'],
            ['code'=>'pharmacie','nom'=>'Pharmacie','couleur'=>'#06B6D4','icone'=>'fa-pills'],
            ['code'=>'sante','nom'=>'Santé','couleur'=>'#DC2626','icone'=>'fa-stethoscope'],
            ['code'=>'sport','nom'=>'Sport','couleur'=>'#EA580C','icone'=>'fa-dumbbell'],
            ['code'=>'telecom','nom'=>'Télécommunications','couleur'=>'#1D4ED8','icone'=>'fa-tower-broadcast'],
            ['code'=>'textile','nom'=>'Textile','couleur'=>'#A855F7','icone'=>'fa-shirt'],
            ['code'=>'tourisme','nom'=>'Tourisme','couleur'=>'#0EA5E9','icone'=>'fa-plane'],
            ['code'=>'autre','nom'=>'Autre','couleur'=>'#64748B','icone'=>'fa-tag'],
        ];

        $now = now();
        foreach ($secteurs as $i => $row) {
            DB::table('intranet_secteurs_activite')->updateOrInsert(
                ['code' => $row['code']],
                array_merge($row, ['ordre' => $i, 'created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
