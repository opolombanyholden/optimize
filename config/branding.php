<?php

/**
 * Personnalisation par distribution.
 * Chaque instance d'OptimiZe (déploiement client, marque blanche) peut définir
 * son propre nom de produit et sa couleur primaire via le .env.
 */
return [
    'name'  => env('BRAND_NAME', 'OptimiZe'),
    'color' => env('BRAND_COLOR', '#0D9488'),
];
