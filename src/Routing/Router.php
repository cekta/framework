<?php

declare(strict_types=1);

namespace Cekta\Framework\Routing;

use FastRoute\Dispatcher;
use FastRoute\FastRoute;

class Router
{
    /**
     * @var array<array{
     *     method: string,
     *     pattern: string,
     *     handler: string,
     *     middlewares: array
     * }>
     */
    private array $routes;

    public function __construct(
        private string $handler_404 = NotFound::class,
        private array $middlewares_404 = [],
        private string $handler_405 = NotAllowed::class,
        private array $middlewares_405 = [],
    ) {
    }
    
    public function setRoute404(string $handler_404, array $middleware_404 = []): self
    {
        $this->handler_404 = $handler_404;
        $this->middlewares_404 = $middleware_404;
        return $this;
    }

    public function setRoute405(string $handler_405, array $middleware_405 = []): self
    {
        $this->handler_405 = $handler_405;
        $this->middlewares_405 = $middleware_405;
        return $this;
    }

    /**
     * @return array{
     *     handler: string,
     *     middlewares: string[]
     * }
     */
    public function getRoute405(): array
    {
        return [
            'handler' => $this->handler_405,
            'middlewares' => $this->middlewares_405
        ];
    }

    /**
     * @return array{
     *     handler: string,
     *     middlewares: string[]
     * }
     */
    public function getRoute404(): array
    {
        return [
            'handler' => $this->handler_404,
            'middlewares' => $this->middlewares_404
        ];
    }

    public function get(string $pattern, string $handler, array $middlewares = []): self
    {
        return $this->route('GET', $pattern, $handler, $middlewares);
    }

    public function post(string $pattern, string $handler, array $middlewares = []): self
    {
        return $this->route('POST', $pattern, $handler, $middlewares);
    }

    public function patch(string $pattern, string $handler, array $middlewares = []): self
    {
        return $this->route('PATCH', $pattern, $handler, $middlewares);
    }

    public function put(string $pattern, string $handler, array $middlewares = []): self
    {
        return $this->route('PUT', $pattern, $handler, $middlewares);
    }

    public function head(string $pattern, string $handler, array $middlewares = []): self
    {
        return $this->route('HEAD', $pattern, $handler, $middlewares);
    }

    public function options(string $pattern, string $handler, array $middlewares = []): self
    {
        return $this->route('OPTIONS', $pattern, $handler, $middlewares);
    }

    // аналогично другие типы маршрутов

    public function build(): Dispatcher
    {
        $fast_route = FastRoute::recommendedSettings(function (\FastRoute\RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['pattern'], [
                    'handler' => $route['handler'],
                    'middlewares' => $route['middlewares']
                ]);
            }
        }, '')->disableCache(); // cache not required, created on boot
        return $fast_route->dispatcher();
    }

    private function route(string $method, string $pattern, string $handler, array $middlewares): self
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
        return $this;
    }

}
