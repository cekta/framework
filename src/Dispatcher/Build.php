<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\DI\Project;
use Cekta\Framework\Dispatcher;

class Build implements Dispatcher
{
    public function dispatch(Project $project): void
    {
        $project->clean();
        $project->container();
        
        echo "Generated optimized Container and discover files" . PHP_EOL;
    }
}
