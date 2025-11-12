<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Psr\Container\ContainerInterface;

interface ContainerFactory
{
    public function createContainer(): ContainerInterface;
}
