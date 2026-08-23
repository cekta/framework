<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Exception;

use JetBrains\PhpStorm\Pure;

class MigrationNotExist extends \RuntimeException
{
    #[Pure]
    public function __construct(string $migration, string $MigrationNotExist)
    {
        parent::__construct(
            sprintf(
                "Cannot resolve dependencies: migration '%s' requires a non-existent migration '%s'.",
                $migration,
                $MigrationNotExist
            )
        );
    }
}
