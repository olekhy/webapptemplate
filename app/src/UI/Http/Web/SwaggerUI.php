<?php

declare(strict_types=1);

namespace App\UI\Http\Web;

use App\App;
use App\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SwaggerUI extends BaseController
{
    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        $target = $request->getRequestTarget();
        if (\substr($target, -\strlen('.json')) === '.json') {
            return $this->renderJson($this->getSchema());
        }

        $this->disableLayout();
        $spec = $this->getSchema();
        $spec = \json_encode($spec, \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES | \JSON_HEX_TAG);

        $model = [
            'webSiteTitle' => '@PROJECT_NAME@ REST API Dokumentation',
            'spec'         => $spec,
        ];

        return $this->render($model, 200, 'swagger-ui');
    }

    /** @return array<mixed> */
    private function getSchema() : array
    {
        $swaggerSchemeFile = App::getConfig('swagger_scheme_file');
        $swaggerScheme     = \file_get_contents($swaggerSchemeFile);
        \assert($swaggerScheme !== false);

        return \json_decode($swaggerScheme, true, 512, \JSON_THROW_ON_ERROR);
    }
}
