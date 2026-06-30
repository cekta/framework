<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TaskHandler
{
    public function __construct(
        public string $taskName,
    ) {
    }
}
