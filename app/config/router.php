<?php

declare(strict_types=1);

use App\Routes;
use App\UI\Http\Rest\Ping;
use App\UI\Http\Web\SwaggerUI;
use FastRoute\RouteCollector;

return static function (RouteCollector $router) : void {
    $router->get(Routes::SWAGGER_UI, SwaggerUI::class);
    $router->get(Routes::SWAGGER_UI_SCHEMA, SwaggerUI::class);
    $router->addGroup(
        '/api',
        static function (RouteCollector $routeCollector) : void {
            $routeCollector->get(Routes::PING, Ping::class);
        }
    );
};
