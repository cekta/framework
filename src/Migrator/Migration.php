<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator;

interface Migration
{
    public function up(): void;

    public function down(): void;

    /**
     * @return int unique ascending identifier, unix timestamp normal choice, date +%s
     */
    public function priority(): int;
}
