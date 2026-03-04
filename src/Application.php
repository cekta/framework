<?php

declare(strict_types=1);

namespace Cekta\Framework;

readonly class Application
{
    /**
     * @param array<string, Dispatcher> $dispatchers
     */
    public function __construct(private array $dispatchers)
    {
    }

    public function handle(string $mode, Project $project): void
    {
        if (!array_key_exists($mode, $this->dispatchers)) {
            throw new \RuntimeException(
                "$mode invalid dispatcher, available "
                . implode(', ', array_keys($this->dispatchers))
            );
        }
        $this->dispatchers[$mode]->dispatch($project);
        // handle all errors
    }
}
