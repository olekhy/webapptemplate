<?php

declare(strict_types=1);

namespace App\View;

use RuntimeException;

final class Helpers
{
    /** @var array<string,string|\Closure> */
    private array $helpers;

    /** @param array<string,string|\Closure> $helpers */
    public function __construct(array $helpers)
    {
        $this->helpers = $helpers;
    }

    /**
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (! isset($this->helpers[$name])) {
            throw new RuntimeException(\sprintf('Unbekannter View Helper %s', $name));
        }

        if ($this->helpers[$name] instanceof \Closure) {
            return $this->helpers[$name](...$arguments);
        }

        if (\method_exists($this->helpers[$name], '__invoke')) {
            return (new $this->helpers[$name]())(...$arguments);
        }

        return new $this->helpers[$name](...$arguments);
    }
}
