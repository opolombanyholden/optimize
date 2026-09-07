<?php

namespace Database\Seeders;

use App\Models\NatureIntervention;
use Illuminate\Database\Seeder;

class NatureInterventionSeeder extends Seeder
{
    public function run(): void
    {
        $natures = [
            [
                'code' => 'preventive',
                'libelle' => 'Préventive',
                'description' => "Intervention planifiée pour éviter l'apparition d'une panne (entretien périodique, contrôle, calibrage).",
                'couleur' => '#0891B2',
            ],
            [
                'code' => 'corrective',
                'libelle' => 'Corrective',
                'description' => "Intervention en réponse à une panne ou un dysfonctionnement constaté, pour rétablir le fonctionnement nominal.",
                'couleur' => '#D97706',
            ],
            [
                'code' => 'curative',
                'libelle' => 'Curative',
                'description' => "Réparation en profondeur après un incident majeur — remplacement de composant, remise à niveau complète.",
                'couleur' => '#DC2626',
            ],
            [
                'code' => 'ameliorative',
                'libelle' => 'Améliorative',
                'description' => "Modification ou upgrade visant à améliorer les performances, la fiabilité ou l'ergonomie d'un équipement existant.",
                'couleur' => '#7C3AED',
            ],
            [
                'code' => 'inspection',
                'libelle' => 'Inspection / Diagnostic',
                'description' => "Visite technique de contrôle ou diagnostic sans intervention réparatrice.",
                'couleur' => '#64748B',
            ],
            [
                'code' => 'installation',
                'libelle' => 'Installation / Mise en service',
                'description' => "Pose d'un nouvel équipement, raccordement, paramétrage initial et mise en service.",
                'couleur' => '#059669',
            ],
        ];

        foreach ($natures as $n) {
            NatureIntervention::firstOrCreate(
                ['code' => $n['code']],
                array_merge($n, ['actif' => true]),
            );
        }
    }
}
