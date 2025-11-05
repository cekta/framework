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
    private Dispatcher $dispatcher;

    public function __construct(
        private readonly string $handler_404 = NotFound::class,
        private readonly array $middlewares_404 = [],
        private readonly string $handler_405 = NotAllowed::class,
        private readonly array $middlewares_405 = [],
    ) {
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

    public function buildDispatcher(): Dispatcher
    {
        if (empty($this->dispacher)) {
            $fast_route = FastRoute::recommendedSettings(function (\FastRoute\RouteCollector $r) {
                foreach ($this->routes as $route) {
                    $r->addRoute($route['method'], $route['pattern'], [
                        'handler' => $route['handler'],
                        'middlewares' => $route['middlewares']
                    ]);
                }
            }, '')->disableCache();
            $this->dispatcher = $fast_route->dispatcher();
        }
        return $this->dispatcher;
    }

    /**
     * @param string $httpMethod (GET, POST, PATCH, HEAD, OPTIONS, PUT)
     * @param string $uri
     * @return array{
     *     handler: string,
     *     middlewares: string[],
     *     attributes: array<string, mixed>
     * }
     */
    public function dispatch(string $httpMethod, string $uri): array
    {
        $routeInfo = $this->buildDispatcher()->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                return [
                    'handler' => $this->handler_404,
                    'middlewares' => $this->middlewares_404,
                    'attributes' => [],
                ];
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                return [
                    'handler' => $this->handler_405,
                    'middlewares' => $this->middlewares_405,
                    'attributes' => ['allowed' => $routeInfo[1]],
                ];
            case \FastRoute\Dispatcher::FOUND:
                /** @var array{
                 *      handler: string,
                 *      middlewares: array<string>
                 *     } $route
                 */
                $route = $routeInfo[1];
                return [
                    'handler' => $route['handler'],
                    'middlewares' => $route['middlewares'],
                    'attributes' => $routeInfo[2],
                ];
        }
        throw new \RuntimeException('dispatch fail');
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
