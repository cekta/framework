<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Unit\Queue;

use Cekta\Framework\Queue\Producer;
use Cekta\Framework\Queue\TaskRepository;
use Cekta\Framework\Test\Fixture\ExampleHandler;
use Cekta\Framework\Test\Fixture\ExampleTask;
use JsonSerializable;
use Mockery;
use Mockery\MockInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use RuntimeException;
use Testo\Assert;
use Testo\Expect;
use Testo\Lifecycle\AfterTest;

class ProducerTest
{
    private Producer $producer;
    private UuidFactory&MockInterface $uuidFactory;

    private TaskRepository&MockInterface $repository;

    public function __construct()
    {
        $this->uuidFactory = mock(UuidFactory::class);
        $this->repository = mock(TaskRepository::class);
        $handlers = [
            ExampleTask::class => ExampleHandler::class
        ];
        $this->producer = new Producer($this->repository, $this->uuidFactory);
    }

    #[AfterTest]
    public function afterTest(): void
    {
        Mockery::close();
    }

    public function testPush(): void
    {
        $task = new ExampleTask('test payload');
        $uuid = Uuid::fromString('019f42cb-6262-77d2-9382-77720137ad5e');
        $this->uuidFactory->allows(['uuid7' => $uuid]);
        $this->repository
            ->expects()
            ->push($uuid->toString(), $task)
            ->once();
        $result = $this->producer->produce($task);
        Assert::equals($result, $uuid->toString());
    }

    public function testPushNotFoundHandler()
    {
        Expect::exception(RuntimeException::class);
        $task = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return 'nothing';
            }
        };
        $this->producer->produce($task);
    }
}
