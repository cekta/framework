<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\Project;
use Cekta\Framework\Dispatcher;
use Symfony\Component\Console\Application;

class Cli implements Dispatcher
{
    public function serve(Project $project): void
    {
        $project->createContainer()
            ->get(Application::class)
            ->run();
    }
}
