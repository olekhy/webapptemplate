<?php

declare(strict_types=1);

namespace App\Tests;

use App\View\Helpers;
use PHPUnit\Framework\TestCase;

final class ViewHelpersAwareTest extends TestCase
{
    /** @test */
    public function fehlerWirdProduziertBeimHolenUnbekannterViewHelper() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectErrorMessage(\sprintf('Unbekannter View Helper %s', 'unknown'));
        $helpers            = [];
        $viewHelpersFactory = new Helpers($helpers);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $viewHelpersFactory->unknown();
    }

    /** @test */
    public function viewHelperWirdProduziertAusClosure() : void
    {
        $helpers            = [
            'toUC' => static fn($param = null) => $param ? \strtoupper($param) : '',
        ];
        $viewHelpersFactory = new Helpers($helpers);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $fact = $viewHelpersFactory->toUC('arg1');
        self::assertSame('ARG1', $fact);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        self::assertSame('', $viewHelpersFactory->toUC());
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        self::assertSame('', $viewHelpersFactory->toUC(false));
    }

    /** @test */
    public function viewHelperWirdProduziertAusClass() : void
    {
        $helper = new class () {
            public function __toString() : string
            {
                return 'test ok';
            }
        };

        $helpers            = [
            'class' => \get_class($helper),
        ];
        $viewHelpersFactory = new Helpers($helpers);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $fact = $viewHelpersFactory->class();
        self::assertEquals('test ok', $fact);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $helper1 = $viewHelpersFactory->class();
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $helper2 = $viewHelpersFactory->class();
        self::assertNotSame($helper1, $helper2);
    }

    /** @test */
    public function viewHelperWirdProduziertAusInvokableClass() : void
    {
        $helper = new class () {
            public function __invoke(string $arg) : string
            {
                return $arg . ' test ok';
            }
        };

        $helpers            = [
            'class' => \get_class($helper),
        ];
        $viewHelpersFactory = new Helpers($helpers);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $fact = $viewHelpersFactory->class('Helper');
        self::assertEquals('Helper test ok', $fact);
    }
}
