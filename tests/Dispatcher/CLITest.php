<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Dispatcher\CLI;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class CLITest extends TestCase
{
    public function test(): void
    {
        Assert::assertInstanceOf(Dispatcher::class, new CLI());
    }
}
