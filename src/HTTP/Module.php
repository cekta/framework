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

/**
 * @phpstan-type state array{
 *       entries?: array<string>,
 *       routes?: array<array{method: string, pattern: string, handler: string, middlewares: array<string>}>
 *   }
 */
class Module implements \Cekta\Framework\Contract\Module
{
    /**
     * @var state
     */
    private array $state = [];

    /**
     * @inheritDoc
     */
    public function onCreate(string $encoded_module): array
    {
        return [
            Router::class => new Lazy\Closure(function () use ($encoded_module) {
                $router = new Router(NotFound::class, NotAllowed::class);
                /** @var state $state */
                $state = json_decode($encoded_module, true);
                $routes = $state['routes'] ?? [];
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
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuild(string $encoded_module): array
    {
        /** @var state $state */
        $state = json_decode($encoded_module, true);
        return [
            'entries' => [
                RequestHandlerInterface::class,
                ...($state['entries'] ?? []),
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
     */
    public function getEncodedModule(): string
    {
        $result = json_encode($this->state, JSON_PRETTY_PRINT);
        if ($result === false) {
            throw new InvalidArgumentException('state must be success encoded');
        }
        return $result;
    }
}
