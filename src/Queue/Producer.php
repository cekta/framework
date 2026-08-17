<?php

namespace Cekta\Framework\Queue;

use JsonSerializable;
use Ramsey\Uuid\UuidFactory;
use RuntimeException;

readonly class Producer implements \Cekta\Queue\Producer
{
    public function __construct(
        private TaskRepository $taskRepository,
        private UuidFactory $uuidFactory,
    ) {
    }

    public function produce(JsonSerializable $message): string
    {
        $uuid = $this->uuidFactory->uuid7();
        $this->taskRepository->push($uuid->toString(), $message);
        return $uuid->__toString();
    }
}
