<?php

namespace Cekta\Framework\Test\Fixture;

class ExampleTask implements \JsonSerializable
{
    public function __construct(
        private mixed $payload,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return $this->payload;
    }
}
