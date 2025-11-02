<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Psr\Http\Message\ResponseInterface;

class Response
{
    public static function json($body, int $http_code = 200, array $headers = []): ResponseInterface
    {
        return new \Nyholm\Psr7\Response(
            status: $http_code,
            headers: $headers + ['Content-Type' => 'application/json'],
            body: json_encode($body)
        );
    }

    public static function redirect(string $url, int $http_code = 301): ResponseInterface
    {
        return new \Nyholm\Psr7\Response(
            status: $http_code,
            headers: ['Location' => $url],
        );
    }
}
