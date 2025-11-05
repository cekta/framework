<?php

declare(strict_types=1);

namespace Cekta\Framework\Routing;

use Cekta\Framework\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotAllowed implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return Response::json(['message' =>'405: method not allowed', 'allowed' => $request->getAttribute('allowed')], 405);
    }
}