<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Handles
{
    public function __construct(
        public readonly string $message
    ) {
    }
}
