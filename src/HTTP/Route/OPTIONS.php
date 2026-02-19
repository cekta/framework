<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP\Route;

use Cekta\Framework\HTTP\Route;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class OPTIONS extends Route
{
    public function __construct(string $pattern, array $middlewares = [])
    {
        parent::__construct($pattern, 'OPTIONS', $middlewares);
    }
}
