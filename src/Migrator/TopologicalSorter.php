<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

use Cekta\Framework\Migrator\Exception\CircularReference;
use Cekta\Framework\Migrator\Exception\MigrationNotExist;
use SplQueue;

class TopologicalSorter
{
    /**
     * @param array<string> $migrationInstalled
     * @param array<string, Migration> $migrations
     * @return array<string>
     */
    public function __invoke(
        array $migrationInstalled,
        array $migrations,
    ): array {
        $dependencyMap = [];
        $dependencyMapReverse = [];
        $count = [];
        foreach ($migrations as $fqcn => $migration) {
            $dependencyMap[$fqcn] = [];
            $count[$fqcn] = 0;
            foreach ($migration->require() as $require) {
                if (in_array($require, $migrationInstalled)) {
                    continue;
                }
                if (!array_key_exists($require, $migrations)) {
                    throw new MigrationNotExist($fqcn, $require);
                }
                $dependencyMapReverse[$require][] = $fqcn;
                $dependencyMap[$fqcn][] = $require;
                $count[$fqcn]++;
            }
        }

        $queue = new SplQueue();
        foreach ($count as $fqcn => $value) {
            if ($value === 0) {
                $queue->enqueue($fqcn);
            }
        }

        $result = [];
        while (!$queue->isEmpty()) {
            /** @var string $name */
            $name = $queue->dequeue();
            $result[] = $name;
            foreach ($dependencyMapReverse[$name] ?? [] as $fqcn) {
                $count[$fqcn]--;
                if ($count[$fqcn] === 0) {
                    $queue->enqueue($fqcn);
                }
            }
        }

        if (count($result) !== count(array_keys($dependencyMap))) {
            throw new CircularReference();
        }

        return $result;
    }
}
