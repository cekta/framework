<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\DI\Project;
use Cekta\Framework\Dispatcher;
use RuntimeException;
use Spiral\RoadRunner\Environment;

class RR implements Dispatcher
{
    public function dispatch(Project $project): void
    {
        $dispatchers = [
            Environment\Mode::MODE_HTTP => new Dispatcher\HTTP(),
        ];
        $mode = Environment::fromGlobals()->getMode();
        if (!array_key_exists($mode, $dispatchers)) {
            throw new RuntimeException("$mode run_mode invalid");
        }
        $dispatchers[$mode]->dispatch($project);
    }
}
