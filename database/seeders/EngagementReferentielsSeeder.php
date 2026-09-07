<?php

namespace Database\Seeders;

use App\Models\FrequencePaiement;
use App\Models\TypeEngagement;
use Illuminate\Database\Seeder;

class EngagementReferentielsSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'achat',       'libelle' => 'Achat',                'description' => "Engagement d'achat de biens matériels."],
            ['code' => 'prestation',  'libelle' => 'Prestation',           'description' => "Prestation de service (nettoyage, conseil, etc.)."],
            ['code' => 'cadre',       'libelle' => 'Accord-cadre',         'description' => "Accord-cadre à commandes multiples."],
            ['code' => 'maintenance', 'libelle' => 'Maintenance',          'description' => "Contrat de maintenance corrective/préventive."],
            ['code' => 'licence',     'libelle' => 'Licence / abonnement', 'description' => "Licence logicielle ou abonnement SaaS."],
            ['code' => 'autre',       'libelle' => 'Autre',                'description' => "Toute autre nature d'engagement."],
        ];
        foreach ($types as $i => $t) {
            TypeEngagement::firstOrCreate(
                ['code' => $t['code']],
                array_merge($t, ['ordre' => $i, 'actif' => true]),
            );
        }

        $frequences = [
            ['code' => 'ponctuel',    'libelle' => 'Ponctuel (paiement unique)', 'mois_increment' => null],
            ['code' => 'mensuel',     'libelle' => 'Mensuel',                    'mois_increment' => 1],
            ['code' => 'bimestriel',  'libelle' => 'Bimestriel (tous les 2 mois)','mois_increment' => 2],
            ['code' => 'trimestriel', 'libelle' => 'Trimestriel',                'mois_increment' => 3],
            ['code' => 'semestriel',  'libelle' => 'Semestriel',                 'mois_increment' => 6],
            ['code' => 'annuel',      'libelle' => 'Annuel',                     'mois_increment' => 12],
        ];
        foreach ($frequences as $i => $f) {
            FrequencePaiement::firstOrCreate(
                ['code' => $f['code']],
                array_merge($f, ['ordre' => $i, 'actif' => true]),
            );
        }
    }
}
