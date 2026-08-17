<?php

declare(strict_types=1);

namespace Cekta\Framework\RoadRunner\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Project;
use RuntimeException;
use Spiral\RoadRunner\Environment;

class RR implements Dispatcher
{
    public function dispatch(Project $project): int
    {
        $dispatchers = [
            Environment\Mode::MODE_HTTP => new HTTP(),
        ];
        $mode = Environment::fromGlobals()->getMode();
        if (!array_key_exists($mode, $dispatchers)) {
            throw new RuntimeException("$mode run_mode invalid");
        }
        return $dispatchers[$mode]->dispatch($project);
    }
}
