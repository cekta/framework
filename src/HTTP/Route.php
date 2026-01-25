<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class Route
{
    public function __construct(
        public string $pattern,
        public string $method = 'GET',
        public array $middlewares = []
    ) {
    }
}
