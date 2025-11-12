<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Cekta\DI\Lazy;
use Cekta\DI\Rule;

interface ServiceProvider
{
    /**
     * @return array<string, mixed|Lazy>
     */
    public function params(): array;

    /**
     * @return array{
     *     containers?: string[],
     *     rules?: Rule[],
     *     alias?: array<string, string>
     * }
     */
    public function register(): array;
}
