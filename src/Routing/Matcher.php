<?php

declare(strict_types=1);

namespace Cekta\Framework\Routing;

use Cekta\Routing\MatcherInterface;
use Cekta\Routing\Result;
use Cekta\Routing\ResultInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class Matcher implements MatcherInterface
{
    public function __construct(
        private Router $router,
        private HandlerLocator $handler_locator,
        private MiddlewareLocator $middleware_locator,
    ) {
    }

    public function match(ServerRequestInterface $request): ResultInterface
    {
        $routeInfo = $this->router->build()->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $route = $this->router->getRoute404();
                return $this->resultFactory($route['handler'], $route['middlewares']);
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $route = $this->router->getRoute405();
                return $this->resultFactory($route['handler'], $route['middlewares'], ['allowed' => $routeInfo[1]]);
            case \FastRoute\Dispatcher::FOUND:
                /** @var array{
                 *      handler: string,
                 *      middlewares: array<string>
                 *     } $route
                 */
                $route = $routeInfo[1];
                return $this->resultFactory($route['handler'], $route['middlewares'], $routeInfo[2]);
        }
        throw new \RuntimeException('routing fail');
    }

    private function resultFactory(string $handler, array $middlewares = [], array $attributes = []): ResultInterface
    {
        $located_middlewares = [];
        foreach ($middlewares as $middleware_name) {
            $located_middlewares[] = $this->middleware_locator->get($middleware_name);
        }
        return new Result(
            $this->handler_locator->get($handler),
            $located_middlewares,
            $attributes
        );
    }

}
