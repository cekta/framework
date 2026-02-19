<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

use Cekta\Routing\MatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class Application implements RequestHandlerInterface
{
    public function __construct(private MatcherInterface $matcher)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $this->matcher->match($request);
        $pipeline = new Pipeline($route->getHandler(), ...$route->getMiddlewares());
        foreach ($route->getAttributes() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }
        return $pipeline->handle($request);
    }
}
