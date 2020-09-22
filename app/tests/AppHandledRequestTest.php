<?php

declare(strict_types=1);

namespace App\Tests;

use Aidphp\Http\Response;
use App\App;
use App\ExceptionHandler;
use FastRoute\Dispatcher;
use Interop\Http\EmitterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

final class AppHandledRequestTest extends TestCase
{
    /** @test */
    public function undReturnEinHandlerUndEmitResponse() : void
    {
        $config       = [
            'router'            => static fn() => null,
            'view'              => [
                'layout'     => '',
                'templates'  => '',
                'attributes' => [],
            ],
            'viewHelpers'       => [],
            'exception_handler' => new ExceptionHandler(),
        ];
        $view         = $this->createMock(PhpRenderer::class);
        $handlerClass = new class ($view) {
            public PhpRenderer $view;

            public function __construct(PhpRenderer $view)
            {
                $this->view = $view;
            }

            public function __invoke(ServerRequestInterface $request) : ResponseInterface
            {
                return new Response();
            }
        };

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with('GET', '/')
            ->willReturn(
                [
                    0 => Dispatcher::FOUND,
                    1 => \get_class($handlerClass),
                    2 => ['rp' => 'abc'],
                ]
            );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::exactly(2))->method('getMethod')->willReturn('GET');
        $request->expects(self::exactly(2))->method('getRequestTarget')->willReturn('/?param=val');
        $request->expects(self::exactly(2))->method('withAttribute')->willReturnSelf();
        $emitter = $this->createMock(EmitterInterface::class);
        $emitter->expects(self::once())->method('emit')->with(self::isInstanceOf(ResponseInterface::class));
        $app      = new App($config, $dispatcher, $view, $emitter);
        $response = $app->handleRequest($request);
        self::assertSame(200, $response->getStatusCode());
        $app->run($request);
    }

    /** @test */
    public function undProduziertFehler405MethodNotAllowed() : void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionCode(405);
        $this->expectExceptionMessage(
            'Leider angefragte HTTP Method "GET" nicht erlaubt. Erlaubt sind "POST".'
        );
        $config = [
            'router'            => static fn() => null,
            'view'              => [
                'layout'     => '',
                'templates'  => '',
                'attributes' => [],
            ],
            'viewHelpers'       => [],
            'exception_handler' => new ExceptionHandler(),
        ];
        $view   = $this->createMock(PhpRenderer::class);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with('GET', '/')
            ->willReturn(
                [
                    0 => Dispatcher::METHOD_NOT_ALLOWED,
                    1 => ['POST'],
                ]
            );

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getMethod')->willReturn('GET');
        $request->expects(self::once())->method('getRequestTarget')->willReturn('/');
        $emitter = $this->createMock(EmitterInterface::class);
        $emitter->expects(self::never())->method('emit');
        $app = new App($config, $dispatcher, $view, $emitter);
        $app->handleRequest($request);
    }

    /** @test */
    public function undProduziertFehler404NotFound() : void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage('Leider angefragte Resource "/" nicht existent!');
        $config = [
            'router'            => static fn() => null,
            'view'              => [
                'layout'     => '',
                'templates'  => '',
                'attributes' => [],
            ],
            'viewHelpers'       => [],
            'exception_handler' => new ExceptionHandler(),
        ];
        $view   = $this->createMock(PhpRenderer::class);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with('GET', '/')
            ->willReturn([0 => Dispatcher::NOT_FOUND]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getMethod')->willReturn('GET');
        $request->expects(self::once())->method('getRequestTarget')->willReturn('/');
        $app = new App($config, $dispatcher, $view);
        $app->handleRequest($request);
    }

    /** @test */
    public function undProduziert500UnbekannterServerFehler() : void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('Unbekannter Server Fehler, Router Problem');
        $config = [
            'router'            => static fn() => null,
            'view'              => [
                'layout'     => '',
                'templates'  => '',
                'attributes' => [],
            ],
            'viewHelpers'       => [],
            'exception_handler' => new ExceptionHandler(),
        ];
        $view   = $this->createMock(PhpRenderer::class);

        $dispatcher = $this->createMock(Dispatcher::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with('GET', '/')
            ->willReturn([]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getMethod')->willReturn('GET');
        $request->expects(self::once())->method('getRequestTarget')->willReturn('/');
        $app = new App($config, $dispatcher, $view);
        $app->handleRequest($request);
    }
}
