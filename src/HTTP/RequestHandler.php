<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

use Cekta\Routing\Nikic\MiddlewareLocator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

readonly class RequestHandler implements RequestHandlerInterface
{
    /**
     * @param Application $application
     * @param MiddlewareLocator $middleware_locator
     * @param array<class-string> $middlewares
     */
    public function __construct(
        private Application $application,
        private MiddlewareLocator $middleware_locator,
        private array $middlewares
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middlewares = [];
        foreach ($this->middlewares as $middleware) {
            $middlewares[] = $this->middleware_locator->get($middleware);
        }
        $pipeline = new Pipeline($this->application, ...$middlewares);
        return $pipeline->handle($request);
    }
}
