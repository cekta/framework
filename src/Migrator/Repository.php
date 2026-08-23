<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

interface Repository
{
    /**
     * @return array<string>
     */
    public function migrations(?int $limit = null): array;

    public function isInstalled(): bool;

    public function install(): void;

    public function up(Migration $migration): void;

    public function down(int $id, Migration $migration): void;
}
