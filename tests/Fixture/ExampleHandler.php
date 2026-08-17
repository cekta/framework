<?php

namespace Cekta\Framework\Test\Fixture;

use Cekta\Queue\Handler;
use Cekta\Queue\Task;

class ExampleHandler implements Handler
{
    public function handle(Task $task): bool
    {
        return true;
    }
}
