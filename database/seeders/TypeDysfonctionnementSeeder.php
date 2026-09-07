<?php

namespace Database\Seeders;

use App\Models\TypeDysfonctionnement;
use Illuminate\Database\Seeder;

class TypeDysfonctionnementSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['libelle' => 'Panne électrique',        'description' => "Coupure de courant, disjoncteur, prise défectueuse, éclairage HS."],
            ['libelle' => 'Panne informatique',      'description' => "Ordinateur, imprimante, réseau, logiciel, périphérique."],
            ['libelle' => 'Climatisation',           'description' => "Climatiseur qui ne refroidit pas, fuite d'eau, télécommande HS."],
            ['libelle' => 'Plomberie',               'description' => "Fuite, robinet cassé, WC bouché, chauffe-eau, évacuation."],
            ['libelle' => 'Menuiserie / Serrurerie', 'description' => "Porte, serrure, fenêtre, meuble abîmé, clé perdue."],
            ['libelle' => 'Mobilier',                'description' => "Chaise, bureau, armoire, tiroir défectueux."],
            ['libelle' => 'Bâtiment / Génie civil',  'description' => "Fissure, infiltration, peinture, faux plafond, revêtement."],
            ['libelle' => 'Véhicule',                'description' => "Panne mécanique, entretien, carrosserie, pneumatique."],
            ['libelle' => 'Équipement bureau',       'description' => "Photocopieur, destructeur, machine à café, réfrigérateur."],
            ['libelle' => 'Sécurité / Incendie',     'description' => "Extincteur, alarme, détecteur, caméra, contrôle d'accès."],
            ['libelle' => 'Nettoyage / Hygiène',     'description' => "Locaux sales, insalubrité, nuisibles, poubelles."],
            ['libelle' => 'Espace vert',             'description' => "Jardin, plantation, tonte, arrosage."],
            ['libelle' => 'Autre',                   'description' => "Toute autre problématique non listée ci-dessus."],
        ];

        foreach ($types as $t) {
            TypeDysfonctionnement::firstOrCreate(
                ['libelle' => $t['libelle']],
                ['description' => $t['description']],
            );
        }
    }
}
