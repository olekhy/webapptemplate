<?php

declare(strict_types=1);

namespace App;

use Aidphp\Http\Emitter;
use App\View\Helpers;
use FastRoute\Dispatcher;
use Interop\Http\EmitterInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;
use function FastRoute\simpleDispatcher;

final class App
{
    /** @var array<mixed> */
    private static array     $config;
    private Dispatcher       $dispatcher;
    private PhpRenderer      $renderer;
    private EmitterInterface $emitter;

    /**
     * @param array<mixed> $config
     */
    public function __construct(
        array $config,
        ?Dispatcher $dispatcher = null,
        ?PhpRenderer $renderer = null,
        ?EmitterInterface $emitter = null
    ) {
        self::$config  = $config;
        $this->emitter = $emitter ?? $this->emitter = new Emitter();
        // php stan prüfung akzeptiert diese block nur wenn anonyme function boolean returned
        \set_error_handler(
            static function ($errorCode, string $errorMessage) : bool {
                throw new \Error($errorMessage, 500);
            }
        );
        $exceptionHandlerClass = self::getConfig('exception_handler');
        $exceptionHandler      = new $exceptionHandlerClass();
        if (! $exceptionHandler instanceof ExceptionHandler) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Ungültige Exception Handler Class, erwartet "%s"',
                    ExceptionHandler::class
                )
            );
        }

        \set_exception_handler($exceptionHandler);

        $routerConfig      = self::getConfig('router');
        $viewConfig        = self::getConfig('view');
        $viewHelpersConfig = self::getConfig('viewHelpers');
        $this->dispatcher  = $dispatcher ?? $this->dispatcher = simpleDispatcher($routerConfig);
        $this->renderer    = $renderer ?? $this->renderer = new PhpRenderer(
            $viewConfig['templates'],
            \array_merge($viewConfig['attributes'], ['helpers' => new Helpers($viewHelpersConfig)]),
            $viewConfig['layout']
        );
    }

    public function run(ServerRequestInterface $request) : void
    {
        $response = $this->handleRequest($request);
        $this->emitter->emit($response);
    }

    public function handleRequest(ServerRequestInterface $request) : ResponseInterface
    {
        $httpMethod = $request->getMethod();
        $uri        = $request->getRequestTarget();
        $position   = \strpos($uri, '?');
        if ($position !== false) {
            $uri = \substr($uri, 0, $position);
        }

        $uri = \rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        if (\count($routeInfo) === 3) {
            [0 => $info, 1 => $handler, 2 => $vars] = $routeInfo;
            if ($info === Dispatcher::FOUND) {
                if ($vars !== null) {
                    foreach ($vars as $name => $value) {
                        $request = $request->withAttribute($name, $value);
                    }
                }

                return (new $handler($this->renderer))($request);
            }
        } elseif (\count($routeInfo) === 2) {
            [0 => $info, 1 => $allowedMethods] = $routeInfo;

            if ($info === Dispatcher::METHOD_NOT_ALLOWED) {
                $message = \sprintf(
                    'Leider angefragte HTTP Method "%s" nicht erlaubt. Erlaubt sind "%s".',
                    $httpMethod,
                    \implode(',', $allowedMethods)
                );

                throw new \Error($message, 405);
            }
        } elseif (\count($routeInfo) === 1 && $routeInfo[0] === Dispatcher::NOT_FOUND) {
            throw new \Error(\sprintf('Leider angefragte Resource "%s" nicht existent!', $uri), 404);
        }

        throw new \Error('Unbekannter Server Fehler, Router Problem', 500);
    }

    /** @return mixed */
    public static function getConfig(?string $partial = null)
    {
        if ($partial !== null && ! isset(self::$config[$partial])) {
            throw new InvalidArgumentException(\sprintf('Unknown config key given "%s".', $partial));
        }

        if (! isset(self::$config) || \count(self::$config) < 1) {
            throw new \RuntimeException(
                'Application config is empty, maybe Application wasn\'t properly instantiated.'
            );
        }

        return $partial !== null ? self::$config[$partial] : self::$config;
    }
}
