<?php

declare(strict_types=1);

namespace Cekta\Framework;

interface Dispatcher
{
    public function serve(Project $project): void;
}
