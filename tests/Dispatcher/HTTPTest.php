<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Dispatcher\HTTP;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class HTTPTest extends TestCase
{
    public function test(): void
    {
        Assert::assertInstanceOf(Dispatcher::class, new HTTP());
    }
}
