<?php

declare(strict_types=1);

namespace Cekta\Framework;

interface Dispatcher
{
    public function dispatch(Project $project): void;
}
