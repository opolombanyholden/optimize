<?php

namespace Database\Seeders;

use App\Models\Compte;
use Illuminate\Database\Seeder;

/**
 * Plan comptable OHADA simplifié pour les écritures de paie (Gabon).
 *
 * Le mapping config/comptabilite.php référence ces codes.
 * Idempotent (firstOrCreate par code).
 */
class ComptesPaieOhadaSeeder extends Seeder
{
    public function run(): void
    {
        // type : 1=banque, 2=caisse, 3=tiers, 4=charge, 5=produit (extension OPTIMIZE)
        $comptes = [
            // Classe 4 — Comptes de tiers (Dettes/Créances)
            ['code' => '421100', 'nom' => 'Personnel - Rémunérations dues',                'type' => 3],
            ['code' => '422100', 'nom' => 'Personnel - Acomptes',                          'type' => 3],
            ['code' => '422200', 'nom' => 'Personnel - Avances sur salaire',               'type' => 3],
            ['code' => '423100', 'nom' => 'Personnel - Oppositions/Saisies-arrêts',        'type' => 3],
            ['code' => '431100', 'nom' => 'CNSS (Sécurité sociale)',                       'type' => 3],
            ['code' => '432100', 'nom' => 'CNAMGS (Assurance maladie)',                    'type' => 3],
            ['code' => '437100', 'nom' => 'FNH (Fonds national habitat)',                  'type' => 3],
            ['code' => '437200', 'nom' => 'CFP (Cotisation formation professionnelle)',    'type' => 3],
            ['code' => '442100', 'nom' => 'État - IRPP (Impôt sur le revenu)',             'type' => 3],
            ['code' => '442200', 'nom' => 'État - Autres taxes salaires (TCS)',            'type' => 3],

            // Classe 6 — Comptes de charges (Dépenses)
            ['code' => '661100', 'nom' => 'Rémunérations du personnel national',           'type' => 4],
            ['code' => '661200', 'nom' => 'Primes du personnel',                            'type' => 4],
            ['code' => '661300', 'nom' => 'Indemnités du personnel',                        'type' => 4],
            ['code' => '661400', 'nom' => 'Heures supplémentaires',                         'type' => 4],
            ['code' => '664100', 'nom' => 'Charges sociales patronales - CNSS',             'type' => 4],
            ['code' => '664200', 'nom' => 'Charges sociales patronales - CNAMGS',           'type' => 4],
            ['code' => '664300', 'nom' => 'Charges sociales patronales - FNH',              'type' => 4],
            ['code' => '664400', 'nom' => 'Charges sociales patronales - CFP',              'type' => 4],

            // Classe 5 — Trésorerie (compte par défaut pour le règlement)
            ['code' => '521100', 'nom' => 'Banque - Compte principal',                      'type' => 1],
            ['code' => '571100', 'nom' => 'Caisse - Espèces',                               'type' => 2],
        ];

        $created = 0;
        foreach ($comptes as $c) {
            $compte = Compte::firstOrCreate(
                ['code' => $c['code']],
                ['nom' => $c['nom'], 'type' => $c['type'], 'solde' => 0]
            );
            if ($compte->wasRecentlyCreated) $created++;
        }

        $this->command?->info("Plan comptable OHADA paie : $created compte(s) créé(s), " . (count($comptes) - $created) . " existant(s).");
    }
}
