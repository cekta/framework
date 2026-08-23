<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Unit\Migrator;

use Cekta\Framework\Migrator\Exception\CircularReference;
use Cekta\Framework\Migrator\Exception\MigrationNotExist;
use Cekta\Framework\Migrator\Migration;
use Cekta\Framework\Migrator\TopologicalSorter;
use Testo\Assert;
use Testo\Expect;

class TopologicalSorterTest
{
    public function testDefaultABC(): void
    {
        $sorter = new TopologicalSorter();
        Assert::equals(
            $sorter(
                [],
                [
                    'A' => $this::makeMigration(['B']),
                    'B' => $this::makeMigration(['C']),
                    'C' => $this::makeMigration(),
                ],
            ),
            ['C', 'B', 'A']
        );
    }

    public function testABCWithInstalledD(): void
    {
        $sorter = new TopologicalSorter();
        Assert::equals(
            $sorter(
                [
                    'D',
                ],
                [
                    'A' => $this::makeMigration(['B', 'D']),
                    'B' => $this::makeMigration(['C']),
                    'C' => $this::makeMigration(),
                ],
            ),
            ['C', 'B', 'A']
        );
    }

    public function testInvalidRequire(): void
    {
        Expect::exception(MigrationNotExist::class);
        (new TopologicalSorter())(
            [
            ],
            [
                'A' => $this::makeMigration(['B']),
            ],
        );
    }

    public function testCircularExecption(): void
    {
        Expect::exception(CircularReference::class);
        (new TopologicalSorter())(
            [
            ],
            [
                'A' => $this::makeMigration(['B']),
                'B' => $this::makeMigration(['A']),
            ],
        );
    }

    private static function makeMigration(array $required = []): Migration
    {
        return new class($required) implements Migration {

            /**
             * @param array<string> $required
             */
            public function __construct(readonly private array $required)
            {
            }

            public function up(): void
            {
            }

            public function down(): void
            {
            }

            public function require(): array
            {
                return $this->required;
            }
        };
    }
}
