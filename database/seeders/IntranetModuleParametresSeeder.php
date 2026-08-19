<?php
namespace Database\Seeders;

use App\Models\Intranet\ModuleParametre;
use Illuminate\Database\Seeder;

class IntranetModuleParametresSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // module => [likes, commentaires, partage, ciblage_users, ciblage_groupes, ciblage_entites, peut_etre_public, moderation]
            'annonces'      => [true,  true,  false, true,  true,  true,  true,  false],
            'news'          => [true,  true,  true,  true,  true,  true,  true,  false],
            'evenements'    => [true,  true,  false, true,  true,  true,  true,  false],
            'courriers'     => [false, false, false, true,  true,  true,  false, false],
            'ressources'    => [false, true,  false, true,  true,  true,  false, false],
            'mediatheque'   => [true,  true,  true,  true,  true,  true,  true,  false],
            'archives'      => [false, false, false, true,  true,  true,  false, false],
            'templates'     => [false, true,  false, true,  true,  true,  true,  false],
            'projets'       => [false, true,  false, true,  true,  false, false, false],
            'taches'        => [false, true,  false, true,  true,  false, false, false],
            'rapports'      => [false, true,  false, true,  true,  true,  false, true ],
            'wiki'          => [true,  true,  true,  true,  true,  true,  true,  false],
            'opportunites'  => [false, true,  false, true,  false, false, false, false],
            'objectifs'     => [false, true,  false, true,  true,  true,  false, false],
            'kpi'           => [false, false, false, true,  true,  true,  false, false],
            'evaluations'   => [false, true,  false, false, false, false, false, true ],
        ];

        foreach ($modules as $module => $params) {
            ModuleParametre::updateOrCreate(
                ['module' => $module],
                [
                    'likes_actifs'           => $params[0],
                    'commentaires_actifs'    => $params[1],
                    'partage_actif'          => $params[2],
                    'ciblage_users'          => $params[3],
                    'ciblage_groupes'        => $params[4],
                    'ciblage_entites'        => $params[5],
                    'peut_etre_public'       => $params[6],
                    'moderation_commentaires'=> $params[7],
                    'actif'                  => true,
                ]
            );
        }
    }
}
