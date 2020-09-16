<?php

declare(strict_types=1);

namespace App;

use Aidphp\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

abstract class BaseController
{
    private PhpRenderer $view;

    public function __construct(PhpRenderer $view)
    {
        $this->view = $view;
    }

    abstract public function __invoke(ServerRequestInterface $request) : ResponseInterface;

    public function setLayout(string $layout) : void
    {
        $this->view->setLayout($layout . '.phtml');
    }

    /**
     * @return array<mixed>
     */
    protected function dataFromRequest(ServerRequestInterface $request) : array
    {
        $getData = $request->getQueryParams();
        if ($request->getMethod() !== 'GET') {
            if (\strcasecmp($request->getHeaderLine('content-type'), 'application/json') === 0) {
                $content  = $request->getBody()->getContents();
                $postData = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
            } else {
                $postData = $request->getParsedBody();
            }
        } else {
            $postData = [];
        }

        return \array_replace_recursive(
            $postData,
            $getData
        );
    }

    /**
     * @param array<mixed> $data
     */
    public function render(
        array $data = [],
        int $httpResponseStatus = 200,
        ?string $template = null
    ) : ResponseInterface {
        $classNameAsArray = \explode('\\', static::class);
        $className        = (string) \end($classNameAsArray);
        $template         = ($template ?? \lcfirst($className)) . '.phtml';
        $response         = new Response($httpResponseStatus);

        if (! isset($data['seitenTitle'])) {
            $data['seitenTitle'] = \ucfirst($className);
        }

        return $this->view->render($response, $template, $data);
    }

    /**
     * @param ?array<mixed> $data
     */
    protected function renderJson(?array $data, int $httpResponseStatus = 200) : ResponseInterface
    {
        $this->disableLayout();
        $jsonString = \json_encode(
            $data,
            \JSON_PRETTY_PRINT | \JSON_ERROR_INVALID_PROPERTY_NAME | \JSON_THROW_ON_ERROR
        );

        $response = (new Response($httpResponseStatus))->withHeader('content-type', 'application/json');
        $response->getBody()->write($jsonString);

        return $response;
    }

    public function disableLayout() : void
    {
        $this->view->setLayout('');
    }
}
