<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\ProjectTest;

use Cekta\Framework\Contract\Module;
use ReflectionClass;

class ExampleModule implements Module
{
    public function __construct(
        private array $create,
        private array $build,
        private string $encoded_module = ''
    ) {
    }

    public function onCreate(string $encoded_module): array
    {
        return $this->create;
    }

    public function onBuild(string $encoded_module): array
    {
        return $this->build;
    }

    public function discover(ReflectionClass $class): void
    {
    }

    public function getEncodedModule(): string
    {
        return $this->encoded_module;
    }
}
