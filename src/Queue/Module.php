<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Framework\Queue\Attribute\Handles;
use Cekta\Queue\Handler;
use ReflectionClass;
use RuntimeException;

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
            HandlerProvider::class . '$handlers' => $cachedData['handlers'],
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
                \Cekta\Queue\Producer::class => Producer::class,
                \Cekta\Queue\Consumer::class => Consumer::class,
                \Cekta\Queue\StaleCleaner::class => StaleCleaner::class
            ],
        ];
    }

    public function discover(ReflectionClass $class): void
    {
        if (!$class->isInstantiable() || !$class->implementsInterface(Handler::class)) {
            return;
        }
        $attributes = $class->getAttributes(Handles::class);
        foreach ($attributes as $attribute) {
            /** @var Handles $handles */
            $handles = $attribute->newInstance();
            if (isset($this->state['handlers'][$handles->message])) {
                throw new RuntimeException(sprintf(
                    'Duplicate handler for message "%s": %s and %s',
                    $handles->message,
                    $this->state['handlers'][$handles->message],
                    $class->getName()
                ));
            }
            $this->state['handlers'][$handles->message] = $class->getName();
        }
    }

    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
    public function getCacheableData(): mixed
    {
        return $this->state;
    }
}
