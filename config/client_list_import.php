<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External list codes → internal client_types.code
    |--------------------------------------------------------------------------
    |
    | Keys are normalized (uppercase, trimmed). Values are internal catalog codes.
    |
    */
    'map' => [
        'PG' => 'CLIENTES',
        'PUBLICO GENERAL' => 'CLIENTES',

        '1' => 'ORO',
        'ORO' => 'ORO',
        'MAYOREO ORO' => 'ORO',

        '2' => 'BRONCE',
        'BRONCE' => 'BRONCE',
        'MAYOREO BRONCE' => 'BRONCE',

        '3' => 'PLATA',
        'PLATA' => 'PLATA',
        'MAYOREO PLATA' => 'PLATA',

        '4' => 'DIAMANTE',
        'DIAMANTE' => 'DIAMANTE',
        'MAYOREO DIAMANTE' => 'DIAMANTE',

        '5' => 'PLATAFORMAS',
        'PLATAFORMAS' => 'PLATAFORMAS',

        '7' => 'COLABORADORES',
        'COLABORADORES' => 'COLABORADORES',

        // Legacy / direct internal codes
        'CLIENTES' => 'CLIENTES',
        'REGULAR' => 'CLIENTES',
        'VIP' => 'VIP',
    ],

    'default_code' => 'CLIENTES',
];
