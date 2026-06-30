<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Psr\Container\ContainerInterface;

interface Project
{
    public function container(bool $forceRecreate = false): ContainerInterface;
}
