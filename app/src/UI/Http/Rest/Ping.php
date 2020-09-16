<?php

declare(strict_types=1);

namespace App\UI\Http\Rest;

use App\App;
use App\BaseController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Ping extends BaseController
{
    public function __invoke(RequestInterface $request) : ResponseInterface
    {
        $data = [
            'time'       => \microtime(true),
            'apiVersion' => App::getConfig('apiVersion'),
        ];

        return $this->renderJson($data);
    }
}
