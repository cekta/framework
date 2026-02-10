<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI;

use InvalidArgumentException;
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
    public function onCreate(string $encoded_module): array
    {
        /** @var state $state */
        $state = json_decode($encoded_module, true);
        return [
            ContainerCommandLoader::class . '$commandMap' => [...($state['command_map'] ?? []), ...$this->command_map],
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuild(string $encoded_module): array
    {
        /** @var state $state */
        $state = json_decode($encoded_module, true);
        return [
            'entries' => [
                Application::class,
                ...(array_values([...($state['command_map'] ?? []), ...$this->command_map])),
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
     */
    public function getEncodedModule(): string
    {
        $result = json_encode($this->state, JSON_PRETTY_PRINT);
        if ($result === false) {
            throw new InvalidArgumentException('state must be success encoded');
        }
        return $result;
    }
}
