<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @param mixed $body @see https://www.php.net/manual/en/function.json-encode.php any default type except resource
     * @param int $http_code @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status
     * @param array $headers
     * @param int $encode_flags @see https://www.php.net/manual/en/function.json-encode.php $flags
     * @return ResponseInterface
     */
    public static function json(
        mixed $body,
        int $http_code = 200,
        array $headers = [],
        int $encode_flags = 0,
    ): ResponseInterface {
        return new \Nyholm\Psr7\Response(
            status: $http_code,
            headers: $headers + ['Content-Type' => 'application/json'],
            body: json_encode($body, $encode_flags)
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
