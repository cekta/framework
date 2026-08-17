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

    /**
     * @param mixed $value
     * @param int $flags
     * @param int<1, max> $depth
     * @return string|false
     */
    public function encode(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags, $depth);
    }

    public function readJsonFile(string $filename): mixed
    {
        $data = file_get_contents($filename);
        if ($data === false) {
            throw new \RuntimeException("$filename must be readable");
        }
        return json_decode($data, true);
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

    public function safeUnlink(string $filename): bool
    {
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return false;
    }
}
