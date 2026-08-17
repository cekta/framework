<?php

declare(strict_types=1);

namespace Cekta\Framework\Test\Feature;

use Cekta\Framework\Queue\TaskRepository;
use Cekta\Framework\Test\Fixture\DbStructure;
use Cekta\Framework\Test\Fixture\ExampleHandler;
use Cekta\Framework\Test\Fixture\ExampleTask;
use Cekta\Queue\Status;
use Lcobucci\Clock\SystemClock;
use PDO;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Testo\Assert;
use Testo\Lifecycle\AfterClass;

class TaskRepositoryTest
{
    private PDO $pdo;
    private SystemClock $clock;
    private DbStructure $structure;
    private TaskRepository $repository;

    public function __construct()
    {
        $this->pdo = new PDO(
            dsn: "pgsql:host=db;dbname=postgres;",
            username: "postgres",
            password: "postgres",
            options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        $this->clock = SystemClock::fromUTC();
        $this->structure = new DbStructure(pdo: $this->pdo);
        $this->structure->down();
        $this->structure->up();
        $this->repository = new TaskRepository(
            pdo: $this->pdo,
            clock: $this->clock,
            logger: new NullLogger(),
        );
    }

    #[AfterClass]
    public function afterClass(): void
    {
        $this->structure->down();
    }

    public function testPush(): void
    {
        $uuid = Uuid::uuid7()->toString();
        $payload = 'some default payload';
        $this->repository->push($uuid, new ExampleTask($payload));

        Assert::array($this->getQueueRow($uuid))->notEmpty('Задача должна появится в очереди');
        $task = $this->getTaskRow($uuid);
        Assert::equals($task['uuid'], $uuid);
        Assert::equals($task['queue_name'], 'queue_default');
        Assert::equals($task['payload'], json_encode($payload));
        Assert::equals($task['status'], Status::PENDING->value);
        Assert::null($task['started_at']);
        Assert::null($task['started_pid']);
        Assert::null($task['finished_at']);
    }

    /**
     * @param string $uuid
     * @return false|array{
     *  uuid: string,
     *  queue_name: string,
     *  fqcn: string,
     *  payload: string,
     *  created_at: string,
     *  started_at: ?string,
     *  started_hostname: ?string,
     *  started_pid: ?int,
     *  finished_at: ?string,
     *  status: string
     *  }
     */
    private function getTaskRow(string $uuid): false|array
    {
        $sth = $this->pdo->prepare("SELECT * FROM tasks WHERE uuid = ?");
        $sth->execute([$uuid]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    private function getQueueRow(string $uuid): false|array
    {
        $sth = $this->pdo->prepare("SELECT * FROM queue_default WHERE uuid = ?");
        $sth->execute([$uuid]);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
