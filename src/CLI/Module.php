<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI;

use ReflectionClass;

class Module implements \Cekta\DI\Module
{
    private array $state = [];

    public function __construct(
        private readonly array $command_map = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function onCreate(string $encoded_module): array
    {
        return [
            ContainerCommandLoader::class . '$commandMap' => $this->command_map,
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuild(string $encoded_module): array
    {
        return [
            'entries' => [
                Application::class,
                ...(array_values($this->command_map)),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getEncodedModule(): string
    {
        return json_encode($this->state, JSON_PRETTY_PRINT);
    }
}
