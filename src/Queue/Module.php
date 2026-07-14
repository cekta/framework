<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Queue\Handler;
use Cekta\Queue\Postgres\Consumer;
use Cekta\Queue\Producer;
use ReflectionClass;

/**
 * @phpstan-type state array{handlers: array<string, string>}
 */
class Module implements \Cekta\Module\Module
{
    /**
     * @var state
     */
    private array $state = [
        'handlers' => [],
    ];

    public function onCreateParameters(mixed $cachedData): array
    {
        /** @var state $cachedData */
        return [
            \Cekta\Queue\Postgres\Producer::class . '$handlers' => $cachedData['handlers'],
        ];
    }

    public function onBuildDefinitions(mixed $cachedData): array
    {
        /** @var state $cachedData */
        return [
            'entries' => [
                Consumer::class,
                ...array_values($cachedData['handlers']),
            ],
            'alias' => [
                Producer::class => \Cekta\Queue\Postgres\Producer::class,
            ],
        ];
    }

    public function discover(ReflectionClass $class): void
    {
        if (
            $class->implementsInterface(Handler::class)
        ) {
            $this->state['handlers'][$class->name::getHandledType()] = $class->name;
        }
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
    public function getCacheableData(): mixed
    {
        return $this->state;
    }
}
