<?php

declare(strict_types=1);

namespace Cekta\Framework\CLI;

use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

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
        return json_encode($this->state, JSON_PRETTY_PRINT);
    }
}
