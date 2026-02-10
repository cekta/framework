<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\HTTP;

use Cekta\Framework\HTTP\Module;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function test(): void
    {
        Assert::assertInstanceOf(\Cekta\Framework\Contract\Module::class, new Module());
    }
}
