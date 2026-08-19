<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IPs / réseaux officiels de l'entreprise
    |--------------------------------------------------------------------------
    |
    | Liste de CIDR ou IPs uniques considérés comme « intranet ».
    | Si l'IP du pointage correspond à l'un de ces réseaux, le pointage
    | est marqué « intranet » et validé automatiquement.
    | Sinon → marqué « teletravail » et requiert validation du N+1.
    |
    | Configurable via .env : POINTAGE_INTRANET_IPS="203.0.113.0/24,198.51.100.42"
    |
    */
    'intranet_ips' => array_filter(array_map('trim', explode(',', (string) env('POINTAGE_INTRANET_IPS', '')))),

    /*
    |--------------------------------------------------------------------------
    | Tolérance « localhost » en dev
    |--------------------------------------------------------------------------
    |
    | Si true, les IPs 127.0.0.1, ::1, et 10.0.0.0/8 / 192.168.0.0/16 sont
    | considérées comme intranet. À désactiver en production.
    |
    */
    'tolerate_local' => env('POINTAGE_TOLERATE_LOCAL', true),

    /*
    |--------------------------------------------------------------------------
    | Plage horaire de pointage acceptée
    |--------------------------------------------------------------------------
    |
    | Si non null, les pointages hors de cette plage sont refusés.
    | Format HH:MM. Ex : ['05:00', '23:00'].
    |
    */
    'horaire_min' => env('POINTAGE_HORAIRE_MIN'),
    'horaire_max' => env('POINTAGE_HORAIRE_MAX'),

    /*
    |--------------------------------------------------------------------------
    | Heures normales par défaut (utilisé si entrée/sortie non précisées)
    |--------------------------------------------------------------------------
    */
    'h_normales_defaut' => env('POINTAGE_H_NORMALES_DEFAUT', 8.0),

    /*
    |--------------------------------------------------------------------------
    | QR code générique (partagé, affiché à l'entrée du bureau)
    |--------------------------------------------------------------------------
    |
    | Tous les employés peuvent scanner ce QR depuis leur téléphone, puis
    | saisissent leur matricule + PIN pour s'identifier. Le token sécurise
    | l'URL : si compromis, l'admin régénère et réimprime le QR.
    |
    | Génération initiale : php artisan tinker
    |   > config(['pointage.qr_generique_token' => bin2hex(random_bytes(16))]);
    | ou via la fonction admin "Régénérer le token générique".
    |
    */
    'qr_generique_token' => env('POINTAGE_QR_GENERIQUE_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Anti-bruteforce PIN
    |--------------------------------------------------------------------------
    */
    'pin_max_tentatives' => env('POINTAGE_PIN_MAX_TENTATIVES', 5),
    'pin_fenetre_secondes' => env('POINTAGE_PIN_FENETRE_SECONDES', 600), // 10 min

];
