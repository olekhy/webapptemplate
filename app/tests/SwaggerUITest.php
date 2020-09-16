<?php

declare(strict_types=1);

namespace App\Tests;

use App\App;
use App\ExceptionHandler;
use App\UI\Http\Web\SwaggerUI;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\PhpRenderer;

final class SwaggerUITest extends TestCase
{
    /** @test */
    public function einErrorWirdProduziertWennSchemaFileInvalid() : void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('file_get_contents(WRONG): failed to open stream: No such file or directory');

        $config = [
            'swagger_scheme_file' => 'WRONG',
            'router'              => static fn() => null,
            'view'                => [
                'layout'     => '',
                'templates'  => '',
                'attributes' => [],
            ],
            'viewHelpers'         => [],
            'exception_handler'   => new ExceptionHandler(),
        ];
        (new App($config));
        $view    = $this->createMock(PhpRenderer::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())->method('getRequestTarget')->willReturn('/doc');
        $controller = new SwaggerUI($view);
        $controller($request);
    }
}
