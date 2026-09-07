<?php

/**
 * Personnalisation par distribution.
 * Chaque instance d'OptimiZe (déploiement client, marque blanche) peut définir
 * son propre nom de produit et sa couleur primaire via le .env.
 */
return [
    'name'  => env('BRAND_NAME') ?: 'OptimiZe',
    // Note : dans .env, la valeur hex DOIT être entre guillemets — sinon le #
    // est interprété comme un commentaire et la valeur devient vide.
    //   BRAND_COLOR="#0D9488"     ← correct
    //   BRAND_COLOR=#0D9488       ← parsé comme vide
    'color' => env('BRAND_COLOR') ?: '#0D9488',
];
