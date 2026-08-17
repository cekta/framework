<?php

declare(strict_types=1);

namespace Cekta\Framework\RoadRunner\Route;

use Cekta\Framework\RoadRunner\Route;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class DELETE extends Route
{
    public function __construct(string $pattern, array $middlewares = [])
    {
        parent::__construct($pattern, 'DELETE', $middlewares);
    }
}
