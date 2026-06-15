<?php

declare(strict_types=1);

namespace Cekta\Framework;

use Closure;
use Psr\Log\LoggerInterface;
use Throwable;

class Worker
{
    private bool $shouldStop = false;
    private Closure $callback;

    public function __construct(
        callable $callback,
        private LoggerInterface $logger,
    ) {
        $this->callback = Closure::fromCallable($callback);
    }

    public function work(): int
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        while (!$this->shouldStop) {
            try {
                call_user_func($this->callback);
            } catch (Throwable $e) {
                $this->logger->error($e);
            }
        }
        return 0;
    }

    public function handleSignal(int $signal): void
    {
        $this->shouldStop = true;
    }
}
