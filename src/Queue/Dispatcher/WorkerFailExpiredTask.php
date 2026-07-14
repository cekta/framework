<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue\Dispatcher;

use Cekta\Framework\Dispatcher;
use Cekta\Framework\Project;
use Cekta\Queue\Consumer;

class WorkerFailExpiredTask implements Dispatcher
{
    private bool $shouldStop = false;
    public function __construct(
        private int $sleepSeconds,
        private int $expiredSeconds
    ) {
    }

    public function dispatch(Project $project): int
    {
        pcntl_async_signals(true);
        $signalHandler = function () {
            $this->shouldStop = true;
        };
        pcntl_signal(SIGTERM, $signalHandler);
        pcntl_signal(SIGINT, $signalHandler);
        $container = $project->container();
        /** @var Consumer $consumer */
        $consumer = $container->get(Consumer::class);
        while (!$this->shouldStop) {
            $consumer->failExpiredTasks($this->expiredSeconds);
            sleep($this->sleepSeconds);
        }
        return 0;
    }
}
