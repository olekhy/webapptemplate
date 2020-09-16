<?php

declare(strict_types=1);

namespace App\Tests\UI\Http\Web;

use App\Tests\UI\WebTestCase;

final class SwaggerUITest extends WebTestCase
{
    /** @test */
    public function dokumentation() : void
    {
        $uri      = '/doc';
        $response = $this->webGetResponse($uri);
        self::assertSame(200, $response->getStatusCode());
        $html = (string) $response->getBody();
        self::assertStringContainsString(
            '<title>@PROJECT_NAME@ REST API Dokumentation</title>',
            $html
        );
    }

    /** @test */
    public function schema() : void
    {
        $uri      = '/doc.json';
        $response = $this->webGetResponse($uri);
        self::assertSame(200, $response->getStatusCode());
        $json           = (string) $response->getBody();
        $jsonSchemaFile = \file_get_contents(__DIR__ . '/../../../../swagger.json');
        self::assertIsString($jsonSchemaFile);
        self::assertEquals(
            \json_decode($json, true, 512, \JSON_THROW_ON_ERROR),
            \json_decode($jsonSchemaFile, true, 512, \JSON_THROW_ON_ERROR)
        );
    }
}
