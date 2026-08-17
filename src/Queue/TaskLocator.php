<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Queue\Task;

class TaskLocator implements \Cekta\Queue\TaskLocator
{
    public function __construct(
        private TaskRepository $taskRepository,
    ) {
    }

    public function findByUuid(string $uuid): ?Task
    {
        return $this->taskRepository->findByUuid($uuid);
    }
}
