<?php

declare(strict_types=1);

namespace App\Tests;

use Aidphp\Http\Response;
use App\BaseController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Views\PhpRenderer;

final class BaseControllerTest extends TestCase
{
    /** @test */
    public function layoutKannGewechseltWerden() : void
    {
        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())->method('setLayout')->with('new-layout.phtml');
        $baseController = new class ($view) extends BaseController {
            public function __invoke(ServerRequestInterface $request) : ResponseInterface
            {
                return new Response();
            }
        };
        $baseController->setLayout('new-layout');
    }

    /** @test */
    public function suspendLayout() : void
    {
        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())->method('setLayout')->with('');
        $baseController = new class ($view) extends BaseController {
            public function __invoke(ServerRequestInterface $request) : ResponseInterface
            {
                return new Response();
            }
        };
        $baseController->disableLayout();
    }

    /** @test */
    public function jsonResponse() : void
    {
        $view = $this->createMock(PhpRenderer::class);
        $view->expects(self::once())->method('setLayout')->with('');
        $baseController = new class ($view) extends BaseController {
            public function __invoke(ServerRequestInterface $request) : ResponseInterface
            {
                return new Response();
            }

            public function test() : ResponseInterface
            {
                $data = ['a' => 'b', 'c' => ['d' => 'e']];

                return $this->renderJson($data);
            }
        };

        $response = $baseController->test();
        self::assertSame(200, $response->getStatusCode());
        // exactly in this form
        $expectation = '{
    "a": "b",
    "c": {
        "d": "e"
    }
}';
        self::assertEquals($expectation, (string) $response->getBody());
    }

    /** @test */
    public function datenAusJsonRequestHolen() : void
    {
        $view    = $this->createMock(PhpRenderer::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getQueryParams')->willReturn(['q' => 'q1', 'mix' => 'qMix']);
        $request->expects(self::once())->method('getHeaderLine')
                ->with('content-type')
                ->willReturn('application/json');
        $streamContent = $this->createMock(StreamInterface::class);
        $jsonPayload   = \json_encode(['p' => 'p1', 'mix' => 'pMix'], \JSON_THROW_ON_ERROR);
        $streamContent->expects(self::once())->method('getContents')->willReturn($jsonPayload);
        $request->expects(self::once())->method('getBody')->willReturn($streamContent);
        $baseController = new class ($view) extends BaseController {
            public function __invoke(ServerRequestInterface $request) : ResponseInterface
            {
                return new Response();
            }

            /** @return array<mixed> */
            public function test(ServerRequestInterface $request) : array
            {
                return $this->dataFromRequest($request);
            }
        };

        $data = $baseController->test($request);
        self::assertEquals(['q' => 'q1', 'p' => 'p1', 'mix' => 'qMix'], $data);
    }

    /** @test */
    public function datenAusRequestHolen() : void
    {
        $view    = $this->createMock(PhpRenderer::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getQueryParams')->willReturn(['q' => 'q1', 'mix' => 'qMix']);
        $request->expects(self::once())->method('getHeaderLine')
                ->with('content-type')
                ->willReturn('multipart/form-data');

        $request->expects(self::once())->method('getParsedBody')->willReturn(['p' => 'p1', 'mix' => 'pMix']);
        $baseController = new class ($view) extends BaseController {
            public function __invoke(ServerRequestInterface $request) : ResponseInterface
            {
                return new Response();
            }

            /** @return array<mixed> */
            public function test(ServerRequestInterface $request) : array
            {
                return $this->dataFromRequest($request);
            }
        };

        $data = $baseController->test($request);
        self::assertEquals(['q' => 'q1', 'p' => 'p1', 'mix' => 'qMix'], $data);
    }
}
