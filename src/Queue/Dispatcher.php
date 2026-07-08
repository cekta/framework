<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Framework\Project;
use Cekta\Queue\Postgres\Consumer;
use Cekta\Queue\Postgres\TaskExecutor;
use Cekta\Queue\Task;
use Psr\Log\LoggerInterface;
use Throwable;

class Dispatcher implements \Cekta\Framework\Dispatcher
{
    private bool $shouldStop = false;

    public function __construct(
        private int $usleepAfterNotihing = 1000 * 300,
        private int $taskMaxTimeSeconds = 60 * 60,
    ) {
    }

    public function dispatch(Project $project): int
    {
        $container = $project->container();
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);
        /** @var Consumer $consumer */
        $consumer = $container->get(Consumer::class);
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
                $task = $consumer->findNext();
                if (null === $task) {
                    $logger->debug('nothing todo');
                    usleep($this->usleepAfterNotihing);
                    continue;
                }
                $pid = pcntl_fork();
                if ($pid === -1) {
                    $logger->emergency('cant create fork', [
                        'task_uuid' => $task->getUuid()
                    ]);
                    exit(1);
                }
                if ($pid === 0) {
                    $this->forkWork($project, $task);
                }
                $this->waitForChild($pid, $task, $logger);
            } catch (Throwable $e) {
                $logger->emergency($e);
                break;
            }
        }
        $logger->info('worker stopped');
        return 0;
    }

    private function waitForChild(int $childPid, Task $task, LoggerInterface $logger): void
    {
        $startTime = time();
        while (true) {
            if (pcntl_waitpid($childPid, $status, WNOHANG) === $childPid) {
                $logger->debug("child done", [
                    'childPid' => $childPid,
                    'hostname' => gethostname(),
                    'task_uuid' => $task->getUuid(),
                ]);
                return;
            }
            if (time() - $startTime >= $this->taskMaxTimeSeconds) {
                $logger->info('task timeout, send SIGKILL', [
                    'task_uuid' => $task->getUuid(),
                    'hostname' => gethostname(),
                    'childPid' => $childPid,
                    'handler' => $task->getHandler(),
                ]);
                posix_kill($childPid, SIGKILL);
                pcntl_waitpid($childPid, $status);
                $logger->debug('task waited');
                return;
            }
            usleep(1000 * 100);
        }
    }

    public function forkWork(Project $project, Task $task): void
    {
        try {
            $container = $project->container();
            /** @var LoggerInterface $logger */
            $logger = $container->get(LoggerInterface::class);
            /** @var TaskExecutor $taskExecutor */
            $taskExecutor = $container->get(TaskExecutor::class);
            $taskExecutor->execute($task);
            $logger->info("task {uuid} was handled by {handler} status is {status}", [
                'uuid' => $task->getUuid(),
                'handler' => $task->getHandler(),
                'status' => $task->getStatus(),
            ]);
        } catch (Throwable $e) {
            exit(1);
        }
        exit(0);
    }
}
