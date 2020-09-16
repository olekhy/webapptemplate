<?php

declare(strict_types=1);

/* phpcs:disable */
if (! \defined('APP_ENV')) {
    \define('APP_ENV', \getenv('APP_ENV') ?? 'dev');
}
/* phpcs:enable */

require __DIR__ . '/../vendor/autoload.php';

use Aidphp\Http\ServerRequestFactory;
use App\App;

$request = (new ServerRequestFactory())->createServerRequestFromGlobals();
$config  = include \dirname(__DIR__) . '/config/main.php';
$app     = new App($config);
$app->run($request);
