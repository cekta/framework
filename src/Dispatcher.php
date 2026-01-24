<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Cekta\DI\Project;

interface Dispatcher
{
    public function dispatch(Project $project): void;
}
