<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Framework\Project;
use Cekta\Queue\Postgres\Consumer;
use Psr\Log\LoggerInterface;
use Throwable;

class Dispatcher implements \Cekta\Framework\Dispatcher
{
    private bool $shouldStop = false;

    public function __construct(
        private int $usleepAfterNotihing = 1000 * 300
    ) {
    }

    public function dispatch(Project $project): int
    {
        $container = $project->container();
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);
        $logger->info('worker started');
        pcntl_async_signals(true);
        $signalHandler = function (int $signal) use ($logger) {
            $signalNames = [
                SIGINT => 'SIGINT',
                SIGTERM => 'SIGTERM',
                SIGHUP => 'SIGHUP',
                SIGUSR1 => 'SIGUSR1',
                SIGUSR2 => 'SIGUSR2',
                SIGQUIT => 'SIGQUIT',
                SIGABRT => 'SIGABRT',
            ];
            $logger->info("{signalName}: {signal} for pid {pid} at {hostname}", [
                'signal' => $signal,
                'signalName' => array_key_exists($signal, $signalNames) ? $signalNames[$signal] : 'unknow signal',
                'pid' => getmypid(),
                'hostname' => gethostname(),
            ]);
            $this->shouldStop = true;
        };
        pcntl_signal(SIGTERM, $signalHandler);
        pcntl_signal(SIGINT, $signalHandler);
        while (!$this->shouldStop) {
            try {
                /** @var Consumer $consumer */
                $consumer = $container->get(Consumer::class);
                $task = $consumer->consume();
                if (null === $task) {
                    $logger->debug('nothing todo');
                    usleep($this->usleepAfterNotihing);
                    continue;
                }
                $logger->info("task {uuid} was handled by {handler} status is {status}", [
                    'uuid' => $task->getUuid(),
                    'handler' => $task->getHandler(),
                    'status' => $task->getStatus(),
                ]);
            } catch (Throwable $e) {
                $logger->emergency($e);
                break;
            }
        }
        $logger->info('worker stopped');
        return 0;
    }
}
