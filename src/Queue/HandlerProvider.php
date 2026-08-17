<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Queue\Handler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

readonly class HandlerProvider implements \Cekta\Queue\HandlerProvider
{
    /**
     * @param ContainerInterface $container
     * @param array<string, string> $handlers messageFQCN => handlerName
     */
    public function __construct(
        private ContainerInterface $container,
        private array $handlers,
    ) {
    }

    public function find(string $fqcn): ?Handler
    {
        if (!array_key_exists($fqcn, $this->handlers)) {
            return null;
        }
        try {
            $handler = $this->container->get($this->handlers[$fqcn]);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            return null;
        }
        if (($handler instanceof Handler) === false) {
            return null;
        }
        return $handler;
    }
}
