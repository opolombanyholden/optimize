<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mapping comptes pour les écritures automatiques de paie
    |--------------------------------------------------------------------------
    |
    | Codes du plan comptable OHADA (Gabon). Voir ComptesPaieOhadaSeeder.
    | Modifiable en .env si une organisation utilise des sous-comptes spécifiques.
    |
    */

    'paie' => [
        // Charges (débit)
        'salaire_base'        => env('CPT_SALAIRE_BASE', '661100'),
        'primes'              => env('CPT_PRIMES', '661200'),
        'indemnites'          => env('CPT_INDEMNITES', '661300'),
        'heures_sup'          => env('CPT_HEURES_SUP', '661400'),
        'charges_pat_cnss'    => env('CPT_CHARGES_PAT_CNSS', '664100'),
        'charges_pat_cnamgs'  => env('CPT_CHARGES_PAT_CNAMGS', '664200'),
        'charges_pat_fnh'     => env('CPT_CHARGES_PAT_FNH', '664300'),
        'charges_pat_cfp'     => env('CPT_CHARGES_PAT_CFP', '664400'),

        // Tiers (crédit)
        'personnel_du'        => env('CPT_PERSONNEL_DU', '421100'),
        'personnel_avances'   => env('CPT_PERSONNEL_AVANCES', '422200'),
        'cnss'                => env('CPT_CNSS', '431100'),
        'cnamgs'              => env('CPT_CNAMGS', '432100'),
        'fnh'                 => env('CPT_FNH', '437100'),
        'cfp'                 => env('CPT_CFP', '437200'),
        'irpp'                => env('CPT_IRPP', '442100'),
        'tcs'                 => env('CPT_TCS', '442200'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Journal par défaut pour les écritures de paie
    |--------------------------------------------------------------------------
    */
    'journal_paie' => env('CPT_JOURNAL_PAIE', 'PAIE'),

];
