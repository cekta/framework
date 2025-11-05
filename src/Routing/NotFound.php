<?php

declare(strict_types=1);

namespace Cekta\Framework\Routing;

use Cekta\Framework\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NotFound implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return Response::json('404: not found', 404);
    }
}
