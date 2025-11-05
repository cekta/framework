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
        $route = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());
        $located_middlewares = [];
        foreach ($route['middlewares'] as $middleware_name) {
            $located_middlewares[] = $this->middleware_locator->get($middleware_name);
        }
        return new Result(
            $this->handler_locator->get($route['handler']),
            $located_middlewares,
            $route['attributes'],
        );
    }

}
