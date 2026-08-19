<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['nom'=>'Contrat',        'icone'=>'fa-file-signature',    'couleur'=>'#4F46E5'],
            ['nom'=>'Devis',          'icone'=>'fa-file-invoice-dollar','couleur'=>'#0891B2'],
            ['nom'=>'Facture',        'icone'=>'fa-receipt',           'couleur'=>'#16A34A'],
            ['nom'=>'Courrier',       'icone'=>'fa-envelope',          'couleur'=>'#7C3AED'],
            ['nom'=>'Rapport',        'icone'=>'fa-file-lines',        'couleur'=>'#D97706'],
            ['nom'=>'PV / CR',        'icone'=>'fa-clipboard-list',    'couleur'=>'#0D9488'],
            ['nom'=>'Note interne',   'icone'=>'fa-note-sticky',       'couleur'=>'#F59E0B'],
            ['nom'=>'Attestation',    'icone'=>'fa-certificate',       'couleur'=>'#DC2626'],
            ['nom'=>'Formulaire',     'icone'=>'fa-list-check',        'couleur'=>'#059669'],
            ['nom'=>'Présentation',   'icone'=>'fa-file-powerpoint',   'couleur'=>'#EA580C'],
            ['nom'=>'Autre',          'icone'=>'fa-file',              'couleur'=>'#64748B'],
        ];

        $now = now();
        foreach ($cats as $i => $cat) {
            DB::table('intranet_template_categories')->updateOrInsert(
                ['nom' => $cat['nom']],
                array_merge($cat, [
                    'slug'       => Str::slug($cat['nom']),
                    'ordre'      => $i,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
