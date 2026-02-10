<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\CLI;

use Cekta\Framework\CLI\Module;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function test(): void
    {
        Assert::assertInstanceOf(\Cekta\Framework\Contract\Module::class, new Module());
    }
}
