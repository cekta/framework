<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Queue\Status;

readonly class StaleCleaner implements \Cekta\Queue\StaleCleaner
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {
    }

    public function clean(int $expiredSecond = 60 * 60 * 4): array
    {
        $result = [];
        foreach ($this->taskRepository->findExpired($expiredSecond) as $task) {
            $this->taskRepository->updateStatus($task->getUuid(), Status::FAIL_STALE);
            $result[] = $task->getUuid();
        }
        return $result;
    }
}
