<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

use Cekta\DI\Lazy;
use Cekta\Framework\HTTP\Handler\NotAllowed;
use Cekta\Framework\HTTP\Handler\NotFound;
use Cekta\Routing\MatcherInterface;
use Cekta\Routing\Nikic\Matcher;
use Cekta\Routing\Nikic\Router;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionAttribute;
use ReflectionClass;

/**
 * @phpstan-type state array{
 *       entries?: array<string>,
 *       routes?: array<array{method: string, pattern: string, handler: string, middlewares: array<string>}>
 *   }
 */
class Module implements \Cekta\Module\Module
{
    /**
     * @var state
     */
    private array $state = [];

    /**
     * @param array<class-string> $middlewares names of middlewares for all application routes
     * @param class-string $handler_404 class handler for 404 page
     * @param class-string $handler_405 class handler for 405 page
     * @param array<class-string> $middlewares_404 names of middlewares for 404 page
     * @param array<class-string> $middlewares_405 names of middlewares for 405 page
     */
    public function __construct(
        private readonly array $middlewares = [],
        private readonly string $handler_404 = NotFound::class,
        private readonly string $handler_405 = NotAllowed::class,
        private readonly array $middlewares_404 = [],
        private readonly array $middlewares_405 = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function onCreateParameters(mixed $cachedData): array
    {
        return [
            Router::class => new Lazy\Closure(function () use ($cachedData) {
                $router = new Router(
                    $this->handler_404,
                    $this->handler_405,
                    $this->middlewares_404,
                    $this->middlewares_405,
                );
                /** @var state $cachedData */
                $routes = $cachedData['routes'] ?? [];
                if (empty($routes)) {
                    throw new InvalidArgumentException('routes is empty');
                }
                foreach ($routes as $route) {
                    $router->route(
                        $route['method'],
                        $route['pattern'],
                        $route['handler'],
                        $route['middlewares']
                    );
                }
                return $router;
            }),
            RequestHandler::class . '$middlewares' => $this->middlewares,
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuildDefinitions(mixed $cachedData): array
    {
        /** @var state $cachedData */
        return [
            'entries' => [
                RequestHandlerInterface::class,
                ...$this->middlewares,
                $this->handler_404,
                ...$this->middlewares_404,
                $this->handler_405,
                ...$this->middlewares_405,
                ...($cachedData['entries'] ?? []),
            ],
            'alias' => [
                RequestHandlerInterface::class => RequestHandler::class,
                MatcherInterface::class => Matcher::class,
            ],
            'singletons' => [
                Router::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
        if (
            $class->implementsInterface(RequestHandlerInterface::class)
            && $class->isInstantiable()
            && !empty($routes = $class->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF))
        ) {
            foreach ($routes as $attr) {
                /** @var Route $route */
                $route = $attr->newInstance();
                $this->state['entries'][] = $class->name;
                $this->state['routes'][] = [
                    'method' => $route->method,
                    'pattern' => $route->pattern,
                    'handler' => $class->name,
                    'middlewares' => $route->middlewares,
                ];
            }
        }
    }

    /**
     * @inheritDoc
     * @noinspection PhpMixedReturnTypeCanBeReducedInspection
     */
    public function getCacheableData(): mixed
    {
        return $this->state;
    }
}
