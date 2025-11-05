<?php

declare(strict_types=1);

namespace Cekta\Framework\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HandlerLocator
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $handleName): RequestHandlerInterface
    {
        return $this->container->get($handleName);
    }
}