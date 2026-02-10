<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Dispatcher\Build;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class BuildTest extends TestCase
{
    public function test(): void
    {
        Assert::assertInstanceOf(Dispatcher::class, new Build());
    }
}
