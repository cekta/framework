<?php

declare(strict_types=1);

namespace Cekta\Framework\Project;

use Cekta\DI\ContainerBuilder;

readonly class Effect
{
    public function fileExists(string $filename): bool
    {
        return file_exists($filename);
    }

    public function write(string $filename, string $data): false|int
    {
        return file_put_contents($filename, $data);
    }

    public function readPHPFile(string $filename): mixed
    {
        return require $filename;
    }

    public function createContainer(string $class, mixed ...$args): mixed
    {
        return new ($class)(...$args);
    }

    /**
     * @param array<string> $entries
     * @param array<string, mixed> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     * @return string
     */
    public function buildContainer(
        array $entries,
        array $params,
        array $alias,
        string $fqcn,
        array $singletons,
        array $factories
    ): string {
        $builder = new ContainerBuilder(
            entries: $entries,
            params: $params,
            alias: $alias,
            fqcn: $fqcn,
            singletons: $singletons,
            factories: $factories,
        );
        return $builder->build();
    }
}
