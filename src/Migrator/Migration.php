<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

interface Migration
{
    public function up(): void;

    public function down(): void;

    /**
     * @return array<class-string> Имена миграций которые должны быть выполнены ДО установки текущей миграции
     */
    public function require(): array;
}
