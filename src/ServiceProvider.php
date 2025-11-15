<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Cekta\DI\Lazy;

interface ServiceProvider
{
    /**
     * @return array<string, mixed|Lazy>
     */
    public function params(): array;

    /**
     * @return array{
     *     containers?: string[],
     *     alias?: array<string, string>
     * }
     */
    public function register(): array;
}
