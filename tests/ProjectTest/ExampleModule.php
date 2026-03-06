<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\ProjectTest;

use Cekta\Module\Module;
use ReflectionClass;

class ExampleModule implements Module
{
    public function __construct(
        private array $create,
        private array $build,
        private array $cachedData = []
    ) {
    }

    public function onCreateParameters(mixed $cachedData): array
    {
        return $this->create;
    }

    public function onBuildDefinitions(mixed $cachedData): array
    {
        return $this->build;
    }

    public function discover(ReflectionClass $class): void
    {
    }

    public function getCacheableData(): mixed
    {
        return $this->cachedData;
    }
}
