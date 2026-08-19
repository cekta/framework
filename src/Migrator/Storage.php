<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

interface Storage
{
    public function execute(Migration $migration): void;

    /**
     * @param array<class-string> $migrations
     * @return array<class-string>
     */
    public function generateToExecute(array $migrations): array;

    public function isInstalled(): bool;

    public function install(): void;

    /**
     * @return array<int, class-string>
     */
    public function getRollbackNames(int $step = 1): array;

    public function rollback(int $id, Migration $migration): void;
}
