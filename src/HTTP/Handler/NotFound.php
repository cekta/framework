<?php

declare(strict_types=1);

namespace Cekta\Framework\HTTP\Handler;

use Cekta\Framework\HTTP\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class NotFound implements RequestHandlerInterface
{
    public function __construct(
        private Response\JSONFactory $factory
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->factory->create(
            ['message' => '404: not found'],
            404
        );
    }
}
