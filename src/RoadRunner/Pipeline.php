<?php

declare(strict_types=1);

namespace Cekta\Framework\RoadRunner;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Pipeline implements RequestHandlerInterface
{
    /**
     * @var array<MiddlewareInterface>
     */
    private array $middlewareInterfaces;

    public function __construct(
        private readonly RequestHandlerInterface $handler,
        MiddlewareInterface ...$middlewareInterfaces
    ) {
        $this->middlewareInterfaces = $middlewareInterfaces;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = current($this->middlewareInterfaces);
        next($this->middlewareInterfaces);
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
        return $this->handler->handle($request);
    }
}
