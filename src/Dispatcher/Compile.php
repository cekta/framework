<?php

declare(strict_types=1);

namespace Cekta\Framework\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Project;

class Compile implements Dispatcher
{
    public function serve(Project $project): void
    {
        $project->compile();
    }
}
