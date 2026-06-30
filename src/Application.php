<?php

declare(strict_types=1);

namespace Cekta\Framework;

use RuntimeException;

readonly class Application
{
    /**
     * @param array<string, Dispatcher> $dispatchers
     */
    public function __construct(private array $dispatchers)
    {
    }

    public function handle(string $mode, ProjectDefault $project): int
    {
        if (!array_key_exists($mode, $this->dispatchers)) {
            $dispatchers = implode(', ', array_keys($this->dispatchers));
            throw new RuntimeException("$mode invalid dispatcher, available $dispatchers");
        }
        return $this->dispatchers[$mode]->dispatch($project);
    }
}
