<?php

declare(strict_types=1);

namespace Cekta\Framework\Migrator\Exception;

class CircularReference extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot resolve dependencies: a circular reference detected in migration dependencies');
    }
}
