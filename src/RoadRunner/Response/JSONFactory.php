<?php

declare(strict_types=1);

namespace Cekta\Framework\RoadRunner\Response;

use InvalidArgumentException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

final readonly class JSONFactory
{
    /**
     * @param mixed $body must json_encode-able @see https://www.php.net/manual/en/function.json-encode.php $value
     * @param int $http_code @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status
     * @param array<string, string> $headers header=>value transform to header:value
     * @param int $encode_flags @see https://www.php.net/manual/en/function.json-encode.php $flags
     * @return ResponseInterface
     */
    public function create(
        mixed $body,
        int $http_code = 200,
        array $headers = [],
        int $encode_flags = 0,
    ): ResponseInterface {
        if (is_resource($body)) {
            throw new InvalidArgumentException("body cant be resource");
        }
        $body = json_encode($body, $encode_flags);
        assert($body !== false);
        return new Response(
            status: $http_code,
            headers: $headers + ['Content-Type' => 'application/json'],
            body: $body
        );
    }
}
