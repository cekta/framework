<?php

declare(strict_types=1);

namespace Cekta\Framework;

readonly class Configuration
{
    public function __construct(
        public string $container_filename,
        public string $container_fqcn,
        public array $containers = [],
        public array $params = [],
        public array $alias = [],
    ) {
    }
}
