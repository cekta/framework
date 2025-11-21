<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP;

use Cekta\Routing\MatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements RequestHandlerInterface
{
    /**
     * @var MatcherInterface
     */
    private $matcher;
    public function __construct(MatcherInterface $matcher)
    {
        $this->matcher = $matcher;
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
