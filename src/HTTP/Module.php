<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

use Cekta\DI\Lazy;
use Cekta\Routing\MatcherInterface;
use Cekta\Routing\Nikic\Matcher;
use Cekta\Routing\Nikic\Router;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionAttribute;
use ReflectionClass;

class Module implements \Cekta\DI\Module
{
    private array $state = [];

    /**
     * @inheritDoc
     */
    public function onCreate(string $encoded_module): array
    {
        return [
            Router::class => new Lazy\Closure(function () use ($encoded_module) {
                $router = new Router(NotFound::class, NotAllowed::class);
                $state = json_decode($encoded_module, true);
                $routes = $state['routes'] ?? [];
                if (empty($routes)) {
                    throw new InvalidArgumentException('routes is empty');
                }
                foreach ($routes as ['method' => $method, 'pattern' => $pattern, 'handler' => $handler, 'middlewares' => $middlewares]) {
                    $router->route($method, $pattern, $handler, $middlewares);
                }
                return $router;
            }),
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuild(string $encoded_module): array
    {
        $state = json_decode($encoded_module, true);
        return [
            'entries' => [
                RequestHandlerInterface::class,
                ...($state[RequestHandlerInterface::class] ?? []),
            ],
            'alias' => [
                RequestHandlerInterface::class => Application::class,
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
                $this->state[RequestHandlerInterface::class][] = $class->name;
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
     */
    public function getEncodedModule(): string
    {
        return json_encode($this->state, JSON_PRETTY_PRINT);
    }
}
