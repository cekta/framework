<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class Route
{
    /**
     * @param string $pattern
     * @param string $method
     * @param array<string> $middlewares
     */
    public function __construct(
        public string $pattern,
        public string $method = 'GET',
        public array $middlewares = []
    ) {
    }
}
