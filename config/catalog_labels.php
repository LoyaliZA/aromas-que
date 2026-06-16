<?php

return [
    'job_positions' => [
        'SELLER' => 'Vendedor (Piso)',
        'MANAGER' => 'Gerente',
        'CHECKER' => 'Checador (Recepción)',
        'ADMIN' => 'Administrador',
        'AUXILIAR' => 'Auxiliar (Anuncios TV)',
    ],
    'departments' => [
        'NONE' => 'Sin Área Específica',
        'AROMAS' => 'Aromas',
        'BELLAROMA' => 'Logística Bellaroma',
        'CALLCENTER' => 'Call Center',
        'CEDIS' => 'CEDIS (Centro de Distribución)',
    ],
    'client_types' => [
        'CLIENTES' => 'Clientes',
        'BRONCE' => 'Bronce',
        'PLATA' => 'Plata',
        'ORO' => 'Oro',
        'DIAMANTE' => 'Diamante',
        'COLABORADORES' => 'Colaboradores',
        'PLATAFORMAS' => 'Plataformas',
    ],
    'protected_catalog_names' => [
        'roles' => ['ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER', 'AUXILIAR', 'BELLAROMA', 'CALLCENTER', 'CEDIS'],
        'job_positions' => ['ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'AUXILIAR'],
        'departments' => ['AROMAS', 'BELLAROMA', 'CALLCENTER', 'CEDIS', 'NONE'],
        'client_types' => ['CLIENTES', 'BRONCE', 'PLATA', 'ORO', 'DIAMANTE', 'COLABORADORES', 'PLATAFORMAS'],
    ],
];
