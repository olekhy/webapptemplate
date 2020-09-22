<?php

declare(strict_types=1);

namespace App\Tests;

use App\App;
use App\ExceptionHandler;
use PHPUnit\Framework\TestCase;

final class AppConfigTest extends TestCase
{
    /** @test */
    public function fehlerBeimHolenConfigVorDemDasAppInstanziiertWar() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Application config is empty, maybe Application wasn\'t properly instantiated.'
        );
        App::getConfig();
    }

    /** @test */
    public function fehlerBeimHolenNichtExistentPartialConfig() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown config key given "key".');
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
        (new App($config));
        App::getConfig('key');
    }

    /** @test */
    public function holenPartialConfig() : void
    {
        $config = [
            'router'            => static fn() => null,
            'view'              => [
                'layout'     => '',
                'templates'  => '',
                'attributes' => [],
            ],
            'viewHelpers'       => [],
            'exception_handler' => new ExceptionHandler(),
            'key'               => 'value',
        ];
        (new App($config));
        $configByKey = App::getConfig('key');
        self::assertSame('value', $configByKey);
    }

    /** @test */
    public function holenConfig() : void
    {
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
        (new App($config));
        $factConfig = App::getConfig();
        self::assertSame($config, $factConfig);
    }
}
