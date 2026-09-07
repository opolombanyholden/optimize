<?php

namespace Database\Seeders;

use App\Models\FamilleDysfonctionnement;
use App\Models\TypeDysfonctionnement;
use Illuminate\Database\Seeder;

class FamilleDysfonctionnementSeeder extends Seeder
{
    public function run(): void
    {
        $familles = [
            'Technique & Énergie'    => ['couleur' => '#F59E0B', 'description' => "Énergie, climatisation, plomberie, sanitaires."],
            'Informatique & Réseau'  => ['couleur' => '#4F46E5', 'description' => "Postes, périphériques, logiciels, connectivité."],
            'Bâtiment & Mobilier'    => ['couleur' => '#7C3AED', 'description' => "Structure du bâtiment, ouvrants, mobilier."],
            'Logistique & Véhicules' => ['couleur' => '#0891B2', 'description' => "Parc automobile, transport, manutention."],
            'Bureautique'            => ['couleur' => '#059669', 'description' => "Équipements bureautiques et périphériques partagés."],
            'Sécurité & Sûreté'      => ['couleur' => '#DC2626', 'description' => "Incendie, contrôle d'accès, vidéosurveillance."],
            'Environnement & Hygiène'=> ['couleur' => '#10B981', 'description' => "Propreté, espaces verts, nuisibles, hygiène."],
            'Divers'                 => ['couleur' => '#64748B', 'description' => "Non catégorisé."],
        ];

        $refs = [];
        foreach ($familles as $libelle => $data) {
            $refs[$libelle] = FamilleDysfonctionnement::firstOrCreate(
                ['libelle' => $libelle],
                $data,
            );
        }

        // Rattachement des types existants aux familles
        $rattachements = [
            'Panne électrique'        => 'Technique & Énergie',
            'Climatisation'           => 'Technique & Énergie',
            'Plomberie'               => 'Technique & Énergie',
            'Panne informatique'      => 'Informatique & Réseau',
            'Menuiserie / Serrurerie' => 'Bâtiment & Mobilier',
            'Mobilier'                => 'Bâtiment & Mobilier',
            'Bâtiment / Génie civil'  => 'Bâtiment & Mobilier',
            'Véhicule'                => 'Logistique & Véhicules',
            'Équipement bureau'       => 'Bureautique',
            'Sécurité / Incendie'     => 'Sécurité & Sûreté',
            'Nettoyage / Hygiène'     => 'Environnement & Hygiène',
            'Espace vert'             => 'Environnement & Hygiène',
            'Autre'                   => 'Divers',
        ];

        foreach ($rattachements as $typeLibelle => $familleLibelle) {
            TypeDysfonctionnement::where('libelle', $typeLibelle)
                ->update(['famille_id' => $refs[$familleLibelle]->id]);
        }
    }
}
