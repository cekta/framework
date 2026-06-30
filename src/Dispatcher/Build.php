<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Project;

class Build implements Dispatcher
{
    public function dispatch(Project $project): int
    {
        $project->container(true);
        return 0;
    }
}
