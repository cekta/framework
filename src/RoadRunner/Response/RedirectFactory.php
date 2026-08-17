<?php

declare(strict_types=1);

namespace Cekta\Framework\RoadRunner\Response;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final readonly class RedirectFactory
{
    public function create(string $url, int $http_code = 301): ResponseInterface
    {
        return new Response(
            status: $http_code,
            headers: ['Location' => $url],
        );
    }
}
