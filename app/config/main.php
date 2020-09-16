<?php

declare(strict_types=1);

use App\ExceptionHandler;

Locale::setDefault('de_DE.utf8');

return [
    'apiVersion'          => '0.1.0',
    'db'                  => include 'db.php',
    'exception_handler'   => ExceptionHandler::class,
    'router'              => include 'router.php',
    'swagger_scheme_file' => __DIR__ . '/../swagger.json',
    'view'                => [
        'templates'  => \dirname(__DIR__) . '/templates/',
        'attributes' => ['webseitenTitle' => '@PROJECT_NAME@ web und REST API application'],
        'layout'     => 'layout.phtml',
    ],
    'viewHelpers'         => [],
];
