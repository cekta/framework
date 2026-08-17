<?php

declare(strict_types=1);

namespace Cekta\Framework\Queue;

use Cekta\Queue\Status;

readonly class Consumer implements \Cekta\Queue\Consumer
{
    public function __construct(
        private TaskRepository $taskRepository,
        private HandlerProvider $handlerProvider,
    ) {
    }

    public function consume(): null|string
    {
        $task = $this->taskRepository->findNext();
        if (null === $task) {
            return null;
        }
        try {
            $handler = $this->handlerProvider->find($task->getFqcn());
            if ($handler === null) {
                $this->taskRepository->updateStatus($task->getUuid(), Status::FAIL_HANDLER_NOT_FOUND);
                return $task->getUuid();
            }
            if ($handler->handle($task)) {
                $this->taskRepository->updateStatus($task->getUuid(), Status::SUCCESS);
            } else {
                $this->taskRepository->updateStatus($task->getUuid(), Status::FAIL);
            }
        } catch (\Throwable) {
            $this->taskRepository->updateStatus($task->getUuid(), Status::FAIL_EXCEPTION);
        }
        return $task->getUuid();
    }
}
