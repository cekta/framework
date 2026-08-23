<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

use Cekta\Framework\Migrator\Command\Migrate;
use ReflectionClass;

/**
 * @phpstan-type state array<string,array<string>>
 */
class Module implements \Cekta\Module\Module
{
    /**
     * @var state
     */
    private array $state = [];

    public function __construct(
        private readonly string $storage = Storage\DB::class
    ) {
    }

    /**
     * @inheritDoc
     */
    public function onCreateParameters(mixed $cachedData): array
    {
        /** @var state $cachedData */
        return [
            Migrate::class . '$migrations' => $cachedData[Migration::class] ?? [],
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
                ...($cachedData[Migration::class] ?? []),
            ],
            'alias' => [
                Repository::class => $this->storage,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
        if (
            $class->implementsInterface(Migration::class)
            && $class->isInstantiable()
        ) {
            $this->state[Migration::class][] = $class->name;
        }
    }

    /**
     * @inheritDoc
     */
    public function getCacheableData(): mixed
    {
        return $this->state;
    }
}
