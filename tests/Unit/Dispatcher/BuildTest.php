<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Unit\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Dispatcher\Build;
use Testo\Assert;

class BuildTest
{
    public function test(): void
    {
        Assert::instanceOf(new Build(), Dispatcher::class);
    }
}
