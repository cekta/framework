<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI;

use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

/**
 * @phpstan-type state array{command_map?: array<string>}
 */
class Module implements \Cekta\Framework\Contract\Module
{
    /**
     * @var state
     */
    private array $state = [];

    /**
     * @param array<string, string> $command_map
     */
    public function __construct(
        private readonly array $command_map = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function onCreateParameters(mixed $cachedData): array
    {
        /** @var state $cachedData */
        return [
            ContainerCommandLoader::class . '$commandMap' => [
                ...($cachedData['command_map'] ?? []),
                ...$this->command_map
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuildDefinitions(mixed $cachedData): array
    {
        /** @var state $cachedData */
        return [
            'entries' => [
                Application::class,
                ...(array_values([...($cachedData['command_map'] ?? []), ...$this->command_map])),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
        if (
            $class->isSubclassOf(Command::class)
            && $class->isInstantiable()
            && !empty($attributes = $class->getAttributes(AsCommand::class))
        ) {
            foreach ($attributes as $attr) {
                /** @var AsCommand $command */
                $command = $attr->newInstance();
                $this->state['command_map'][$command->name] = $class->name;
            }
        }
    }

    /**
     * @inheritDoc
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function getCacheableData(): mixed
    {
        return $this->state;
    }
}
