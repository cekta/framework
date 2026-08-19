<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

use Psr\Container\ContainerInterface;

class MigrationLocator
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function get(string $fqcn): Migration
    {
        try {
            $migration = $this->container->get($fqcn);
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        if (!($migration instanceof Migration)) {
            throw new \RuntimeException('Migration must be instance of ' . Migration::class);
        }
        return $migration;
    }
}
