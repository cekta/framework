<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\DI\Project;
use Cekta\Framework\CLI\Application;
use Cekta\Framework\Dispatcher;

class CLI implements Dispatcher
{
    public function dispatch(Project $project): void
    {
        $project->container()
            ->get(Application::class)
            ->run();
    }
}
